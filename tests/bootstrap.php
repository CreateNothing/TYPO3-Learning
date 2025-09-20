<?php

use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (string) ($_SERVER['APP_DEBUG'] ?? 1);

if ((bool) $_SERVER['APP_DEBUG']) {
    umask(0000);
}

static $migrated = false;

if (!$migrated) {
    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $kernel->boot();

    $application = new Application($kernel);
    $application->setAutoExit(false);
    $application->run(new ArrayInput([
        'command' => 'doctrine:database:create',
        '--if-not-exists' => true,
        '--no-interaction' => true,
    ]));

    $application->run(new ArrayInput([
        'command' => 'doctrine:migrations:migrate',
        '--no-interaction' => true,
    ]));

    $kernel->shutdown();
    $migrated = true;
}
