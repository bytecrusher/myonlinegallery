<?php
declare(strict_types=1);

session_start();

$config = require __DIR__ . '/config/app.php';

$localConfigPath = __DIR__ . '/config/config.php';
if (is_file($localConfigPath)) {
  $localConfig = require $localConfigPath;
  if (is_array($localConfig)) {
    $config = array_replace_recursive($config, $localConfig);
  }
}

require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/gallery.php';

if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!empty($_SESSION['logged_in']) && !empty($_SESSION['last_activity'])) {
  $inactiveFor = time() - (int) $_SESSION['last_activity'];
  if ($inactiveFor > (int) $config['auth']['session_lifetime']) {
    unset($_SESSION['logged_in'], $_SESSION['last_activity']);
    session_regenerate_id(true);
  }
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $submittedToken = (string) ($_POST['csrf_token'] ?? '');

  if (!hash_equals((string) $_SESSION['csrf_token'], $submittedToken)) {
    http_response_code(400);
    $errorMessage = 'Invalid request. Please reload the page.';
  } elseif (isset($_POST['btnlogin'])) {
    if (verify_access_code((string) ($_POST['accesscode'] ?? ''), $config['auth'])) {
      session_regenerate_id(true);
      $_SESSION['logged_in'] = true;
      $_SESSION['last_activity'] = time();
    } else {
      $errorMessage = 'Wrong Access Code';
      usleep((int) $config['auth']['failed_login_delay_us']);
    }
  } elseif (isset($_POST['btnlogout'])) {
    unset($_SESSION['logged_in'], $_SESSION['last_activity']);
    session_regenerate_id(true);
  }
}

$isLoggedIn = !empty($_SESSION['logged_in']);
if ($isLoggedIn) {
  $_SESSION['last_activity'] = time();
}

$galleryPayload = [
  'items' => [],
  'stats' => [
    'imageCount' => 0,
    'mappedCount' => 0,
    'timespanLabel' => null,
  ],
  'map' => $config['map'],
];

if ($isLoggedIn) {
  $galleryPayload = collect_gallery_payload($config);
}

render(__DIR__ . '/templates/page.php', [
  'config' => $config,
  'csrfToken' => (string) $_SESSION['csrf_token'],
  'errorMessage' => $errorMessage,
  'galleryPayload' => $galleryPayload,
  'isLoggedIn' => $isLoggedIn,
]);
