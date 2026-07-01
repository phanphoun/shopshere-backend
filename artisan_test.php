<?php

require __DIR__ . '/../../backend/vendor/autoload.php';

$app = require __DIR__ . '/../../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Illuminate\Foundation\Exceptions\Handler::class
);

$request = Illuminate\Http\Request::create('http://localhost/api/products?min_price=150&per_page=5', 'GET');

$response = $app->handle($request);
echo "STATUS: " . $response->getStatusCode() . PHP_EOL;
echo $response->getContent() . PHP_EOL;
