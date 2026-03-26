<?php
declare(strict_types=1);

return [
  'site' => [
    'title' => 'My Online Gallery',
    'description' => 'Short description of your event.',
    'footer_email' => 'info@derguntmar.de',
    'footer_year' => '2024',
    'base_path' => '',
  ],
  'auth' => [
    'access_code' => 'testaccess',
    'access_code_hash' => null,
    'session_lifetime' => 60 * 60 * 8,
    'failed_login_delay_us' => 300000,
  ],
  'gallery' => [
    'image_directory' => __DIR__ . '/../img',
    'image_url_path' => 'img',
    'cache_file' => __DIR__ . '/../cache/gallery-index.json',
    'thumbnail_directory' => __DIR__ . '/../cache/thumbs',
    'thumbnail_url_path' => 'cache/thumbs',
    'thumbnail_width' => 720,
    'supported_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
  ],
  'map' => [
    'default_center' => [53.03, 8.86],
    'default_zoom' => 10,
    'tile_url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    'tile_attribution' => '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  ],
];
