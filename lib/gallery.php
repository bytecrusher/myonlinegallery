<?php
declare(strict_types=1);

function collect_gallery_payload(array $config): array {
  $galleryConfig = $config['gallery'];
  $imageDirectory = (string) $galleryConfig['image_directory'];

  ensure_directory(dirname((string) $galleryConfig['cache_file']));
  ensure_directory((string) $galleryConfig['thumbnail_directory']);

  $imageFiles = list_gallery_files($imageDirectory, (array) $galleryConfig['supported_extensions']);
  $directorySignature = build_directory_signature($imageFiles);
  $cacheFile = (string) $galleryConfig['cache_file'];

  $cachedPayload = read_gallery_cache($cacheFile, $directorySignature);
  if ($cachedPayload !== null) {
    $cachedPayload['map'] = $config['map'];
    $cachedPayload['cache'] = [
      'source' => 'cache',
      'warmedAt' => $cachedPayload['cache']['warmedAt'] ?? null,
    ];
    return $cachedPayload;
  }

  $items = [];
  $issues = [];
  foreach ($imageFiles as $imageFile) {
    $result = build_gallery_item($imageFile, $config);
    if ($result['item'] !== null) {
      $items[] = $result['item'];
    }

    foreach ($result['issues'] as $issue) {
      $issues[] = $issue;
    }
  }

  usort($items, static function (array $left, array $right): int {
    $leftTimestamp = $left['takenTimestamp'] ?? PHP_INT_MAX;
    $rightTimestamp = $right['takenTimestamp'] ?? PHP_INT_MAX;

    if ($leftTimestamp === $rightTimestamp) {
      return strcmp((string) $left['filename'], (string) $right['filename']);
    }

    return $leftTimestamp <=> $rightTimestamp;
  });

  $stats = build_gallery_stats($items);
  $payload = [
    'items' => $items,
    'stats' => $stats,
    'issues' => summarize_gallery_issues($issues),
    'cache' => [
      'source' => 'fresh',
      'warmedAt' => gmdate(DATE_ATOM),
    ],
    'map' => $config['map'],
  ];

  write_gallery_cache($cacheFile, $directorySignature, [
    'items' => $items,
    'stats' => $stats,
    'issues' => summarize_gallery_issues($issues),
    'cache' => [
      'warmedAt' => gmdate(DATE_ATOM),
    ],
  ]);

  return $payload;
}

function ensure_directory(string $directoryPath): void {
  if (is_dir($directoryPath)) {
    return;
  }

  mkdir($directoryPath, 0775, true);
}

function list_gallery_files(string $imageDirectory, array $supportedExtensions): array {
  if (!is_dir($imageDirectory)) {
    return [];
  }

  $normalizedExtensions = array_map('strtolower', $supportedExtensions);
  $files = [];

  foreach (new DirectoryIterator($imageDirectory) as $fileInfo) {
    if (!$fileInfo->isFile()) {
      continue;
    }

    $extension = strtolower($fileInfo->getExtension());
    if (!in_array($extension, $normalizedExtensions, true)) {
      continue;
    }

    $files[] = $fileInfo->getPathname();
  }

  sort($files);

  return $files;
}

function build_directory_signature(array $imageFiles): string {
  $signatureParts = [];

  foreach ($imageFiles as $imageFile) {
    $signatureParts[] = basename($imageFile) . '|' . filemtime($imageFile) . '|' . filesize($imageFile);
  }

  return sha1(implode(';', $signatureParts));
}

function read_gallery_cache(string $cacheFile, string $directorySignature): ?array {
  if (!is_file($cacheFile)) {
    return null;
  }

  $rawCache = file_get_contents($cacheFile);
  if ($rawCache === false) {
    return null;
  }

  $decodedCache = json_decode($rawCache, true);
  if (!is_array($decodedCache)) {
    return null;
  }

  if (($decodedCache['signature'] ?? '') !== $directorySignature) {
    return null;
  }

  if (!isset($decodedCache['payload']) || !is_array($decodedCache['payload'])) {
    return null;
  }

  return $decodedCache['payload'];
}

function write_gallery_cache(string $cacheFile, string $directorySignature, array $payload): void {
  $cacheData = [
    'signature' => $directorySignature,
    'generatedAt' => gmdate(DATE_ATOM),
    'payload' => $payload,
  ];

  file_put_contents($cacheFile, json_encode($cacheData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function build_gallery_item(string $imagePath, array $config): array {
  $galleryConfig = $config['gallery'];
  $fileName = basename($imagePath);
  $imageUrl = asset_url($galleryConfig['image_url_path'] . '/' . rawurlencode($fileName), $config);
  $imageMetadata = extract_image_metadata($imagePath);
  $issues = [];

  if (($imageMetadata['width'] ?? 0) <= 0 || ($imageMetadata['height'] ?? 0) <= 0) {
    return [
      'item' => null,
      'issues' => [[
        'file' => $fileName,
        'reason' => 'Image dimensions could not be read.',
      ]],
    ];
  }

  $thumbnailResult = generate_thumbnail($imagePath, $fileName, $config);
  if ($thumbnailResult['issue'] !== null) {
    $issues[] = [
      'file' => $fileName,
      'reason' => $thumbnailResult['issue'],
    ];
  }

  return [
    'item' => [
      'filename' => $imageUrl,
      'thumbnail' => $thumbnailResult['url'] ?? $imageUrl,
      'width' => $imageMetadata['width'],
      'height' => $imageMetadata['height'],
      'lat' => $imageMetadata['latitude'],
      'lng' => $imageMetadata['longitude'],
      'takenTimestamp' => $imageMetadata['takenTimestamp'],
      'takenDateLabel' => $imageMetadata['takenDateLabel'],
    ],
    'issues' => $issues,
  ];
}

function build_gallery_stats(array $items): array {
  $mappedCount = 0;
  $timestamps = [];

  foreach ($items as $item) {
    if ($item['lat'] !== null && $item['lng'] !== null) {
      $mappedCount++;
    }

    if ($item['takenTimestamp'] !== null) {
      $timestamps[] = $item['takenTimestamp'];
    }
  }

  sort($timestamps);
  $timespanLabel = null;

  if ($timestamps !== []) {
    $firstDate = (new DateTimeImmutable())->setTimestamp($timestamps[0]);
    $lastDate = (new DateTimeImmutable())->setTimestamp($timestamps[array_key_last($timestamps)]);
    $timespanLabel = $firstDate->format('d M Y');

    if ($firstDate->format('Y-m-d') !== $lastDate->format('Y-m-d')) {
      $timespanLabel .= ' - ' . $lastDate->format('d M Y');
    }
  }

  return [
    'imageCount' => count($items),
    'mappedCount' => $mappedCount,
    'timespanLabel' => $timespanLabel,
  ];
}

function summarize_gallery_issues(array $issues): array {
  return [
    'count' => count($issues),
    'samples' => array_slice($issues, 0, 5),
  ];
}

function extract_image_metadata(string $imagePath): array {
  $imageSize = @getimagesize($imagePath) ?: [0, 0];
  $width = (int) ($imageSize[0] ?? 0);
  $height = (int) ($imageSize[1] ?? 0);
  $exif = function_exists('exif_read_data') ? @exif_read_data($imagePath, null, true) : false;

  $latitude = null;
  $longitude = null;
  if (is_array($exif) && isset($exif['GPS'])) {
    $latitude = get_decimal_coordinate(
      $exif['GPS']['GPSLatitude'] ?? null,
      $exif['GPS']['GPSLatitudeRef'] ?? null
    );
    $longitude = get_decimal_coordinate(
      $exif['GPS']['GPSLongitude'] ?? null,
      $exif['GPS']['GPSLongitudeRef'] ?? null
    );
  }

  $takenTimestamp = null;
  $takenDateLabel = '';
  $takenDateRaw = $exif['EXIF']['DateTimeOriginal'] ?? $exif['IFD0']['DateTime'] ?? null;
  if (is_string($takenDateRaw)) {
    $takenDate = DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $takenDateRaw);
    if ($takenDate instanceof DateTimeImmutable) {
      $takenTimestamp = $takenDate->getTimestamp();
      $takenDateLabel = $takenDate->format('d M Y, H:i');
    }
  }

  return [
    'width' => $width,
    'height' => $height,
    'latitude' => $latitude,
    'longitude' => $longitude,
    'takenTimestamp' => $takenTimestamp,
    'takenDateLabel' => $takenDateLabel,
  ];
}

function get_decimal_coordinate(null|array $coordinateParts, ?string $coordinateReference): ?float {
  if ($coordinateParts === null || $coordinateReference === null) {
    return null;
  }

  $degrees = isset($coordinateParts[0]) ? gps_coordinate_to_float($coordinateParts[0]) : 0.0;
  $minutes = isset($coordinateParts[1]) ? gps_coordinate_to_float($coordinateParts[1]) : 0.0;
  $seconds = isset($coordinateParts[2]) ? gps_coordinate_to_float($coordinateParts[2]) : 0.0;
  $coordinate = $degrees + ($minutes / 60) + ($seconds / 3600);

  if (in_array($coordinateReference, ['W', 'S'], true)) {
    $coordinate *= -1;
  }

  return $coordinate;
}

function gps_coordinate_to_float(string $coordinatePart): float {
  $parts = explode('/', $coordinatePart);

  if (count($parts) === 1) {
    return (float) $parts[0];
  }

  $denominator = (float) ($parts[1] ?? 0.0);
  if ($denominator === 0.0) {
    return 0.0;
  }

  return (float) $parts[0] / $denominator;
}

function generate_thumbnail(string $imagePath, string $fileName, array $config): array {
  if (!extension_loaded('gd')) {
    return [
      'url' => null,
      'issue' => 'GD extension is not available, using original image.',
    ];
  }

  $galleryConfig = $config['gallery'];
  $thumbnailWidth = (int) $galleryConfig['thumbnail_width'];
  $thumbnailDirectory = (string) $galleryConfig['thumbnail_directory'];
  $thumbnailBaseName = sha1($fileName . '|' . filemtime($imagePath) . '|' . filesize($imagePath)) . '.jpg';
  $thumbnailPath = $thumbnailDirectory . '/' . $thumbnailBaseName;

  if (!is_file($thumbnailPath)) {
    $sourceImage = create_source_image($imagePath);
    if ($sourceImage === null) {
      return [
        'url' => null,
        'issue' => 'Thumbnail generation failed for this file type.',
      ];
    }

    $sourceWidth = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);
    if ($sourceWidth <= 0 || $sourceHeight <= 0) {
      imagedestroy($sourceImage);
      return [
        'url' => null,
        'issue' => 'Thumbnail generation failed because image dimensions are invalid.',
      ];
    }

    $targetWidth = min($thumbnailWidth, $sourceWidth);
    $targetHeight = (int) max(1, round(($targetWidth / $sourceWidth) * $sourceHeight));
    $thumbnailImage = imagecreatetruecolor($targetWidth, $targetHeight);

    imagefill($thumbnailImage, 0, 0, imagecolorallocate($thumbnailImage, 18, 22, 27));
    imagecopyresampled(
      $thumbnailImage,
      $sourceImage,
      0,
      0,
      0,
      0,
      $targetWidth,
      $targetHeight,
      $sourceWidth,
      $sourceHeight
    );

    if (!imagejpeg($thumbnailImage, $thumbnailPath, 82)) {
      imagedestroy($thumbnailImage);
      imagedestroy($sourceImage);
      return [
        'url' => null,
        'issue' => 'Thumbnail could not be written to cache.',
      ];
    }

    imagedestroy($thumbnailImage);
    imagedestroy($sourceImage);
  }

  return [
    'url' => asset_url($galleryConfig['thumbnail_url_path'] . '/' . $thumbnailBaseName, $config),
    'issue' => null,
  ];
}

function create_source_image(string $imagePath): mixed {
  $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

  return match ($extension) {
    'jpg', 'jpeg' => @imagecreatefromjpeg($imagePath),
    'png' => @imagecreatefrompng($imagePath),
    'gif' => @imagecreatefromgif($imagePath),
    'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($imagePath) : null,
    default => null,
  };
}
