<?php
/**
 * USE .env file for configuration:
 * TODODIR="todo"
 * LOGGING=true
 * LOGDIR="log".
 */
require __DIR__.'/vendor/autoload.php';

use Katzgrau\KLogger\Logger;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Application;

// Load application .env config file:
$file = __DIR__.'/.env';
if (file_exists($file)) {
    $dotenv = new Dotenv();
    $dotenv->load($file);
}

// Create a logger object:
$logger = null;
if (getenv('LOG')) {
    $logdir = (getenv('LOGDIR')) ?: 'log';
    $logger = new Logger($logdir, Psr\Log\LogLevel::INFO, [
        'extension' => 'log',
    ]);
}

// Create the application:
$app = new Application('TodoApp', '1.0 (stable)');

// Register all commands from src/Commands directory:
foreach (glob('src/Command/*') as $command) {
    $class = 'Console\Command\\'.pathinfo($command)['filename'];
    $app->add(new $class($logger));
}

$app->run();
