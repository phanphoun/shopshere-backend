<?php
$artisan = <<<'PHP'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
PHP;

$path = __DIR__.'/artisan_bootstrap.php';
file_put_contents($path, $artisan);
echo 'wrote_ok';
