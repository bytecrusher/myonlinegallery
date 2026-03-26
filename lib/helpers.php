<?php
declare(strict_types=1);

function esc(string $value): string {
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function asset_url(string $path, array $config): string {
  $basePath = trim((string) ($config['site']['base_path'] ?? ''), '/');
  $normalizedPath = ltrim($path, '/');

  if ($basePath === '') {
    return $normalizedPath;
  }

  return $basePath . '/' . $normalizedPath;
}

function render(string $templatePath, array $data = []): void {
  extract($data, EXTR_SKIP);
  require $templatePath;
}

function verify_access_code(string $submittedCode, array $authConfig): bool {
  $configuredHash = $authConfig['access_code_hash'] ?? null;
  if (is_string($configuredHash) && $configuredHash !== '') {
    return password_verify($submittedCode, $configuredHash);
  }

  return hash_equals((string) ($authConfig['access_code'] ?? ''), $submittedCode);
}
