<?php
declare(strict_types=1);

$root = dirname(__DIR__);

require $root . '/lib/helpers.php';
require $root . '/lib/gallery.php';

$config = require $root . '/config/app.php';
$localConfigPath = $root . '/config/config.php';

if (is_file($localConfigPath)) {
  $localConfig = require $localConfigPath;
  if (is_array($localConfig)) {
    $config = array_replace_recursive($config, $localConfig);
  }
}

$start = microtime(true);
$payload = collect_gallery_payload($config);
$durationMs = (int) round((microtime(true) - $start) * 1000);

echo 'Cache source: ' . ($payload['cache']['source'] ?? 'unknown') . PHP_EOL;
echo 'Images: ' . ($payload['stats']['imageCount'] ?? 0) . PHP_EOL;
echo 'Mapped: ' . ($payload['stats']['mappedCount'] ?? 0) . PHP_EOL;
echo 'Issues: ' . ($payload['issues']['count'] ?? 0) . PHP_EOL;
echo 'Duration: ' . $durationMs . ' ms' . PHP_EOL;

if (!empty($payload['issues']['samples'])) {
  echo PHP_EOL . 'Sample issues:' . PHP_EOL;
  foreach ($payload['issues']['samples'] as $issue) {
    echo '- ' . ($issue['file'] ?? 'unknown file') . ': ' . ($issue['reason'] ?? 'unknown issue') . PHP_EOL;
  }
}
