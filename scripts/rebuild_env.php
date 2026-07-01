<?php
$base = dirname(__DIR__);
$path = $base . '/.env';
$example = $base . '/.env.example';

if (!is_file($example)) {
    fwrite(STDERR, ".env.example missing at $example\n");
    exit(1);
}

$content = file_get_contents($example);
$content = preg_replace('/^APP_URL=.*/m', 'APP_URL=http://127.0.0.1:8000', $content);
$content = preg_replace('/^SESSION_DRIVER=.*/m', 'SESSION_DRIVER=file', $content);
$content = preg_replace('/^SESSION_DOMAIN=.*/m', 'SESSION_DOMAIN=127.0.0.1', $content);

file_put_contents($path, $content);
echo "wrote_ok\n";
