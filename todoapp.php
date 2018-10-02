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
$file = __DIR__.DIRECTORY_SEPARATOR.'.env';
if (file_exists($file)) {
    $dotenv = new Dotenv();
    $dotenv->load($file);
}

// Create a logger object:
$logger = null;
if (getenv('LOG')) {
    $logDir = (getenv('LOGDIR')) ?: 'log';
    if (class_exists('Logger')) {
        $logger = new Logger($logDir, Psr\Log\LogLevel::INFO, [
            'extension' => 'log',
        ]);
    }
    $logger = null;
}

// Create a log file HTML viewer by Twig:
if ($logger) {
    $loader = new Twig_Loader_Filesystem('src'.DIRECTORY_SEPARATOR.'View');
    $twig = new Twig_Environment($loader, ['debug' => true]);
    $twig->addExtension(new Twig_Extension_Debug());

    $logFiles = [];
    $i = 0;

    foreach (glob($logDir.DIRECTORY_SEPARATOR.'*.log') as $logFile) {
        $logFiles[$i]['file'] = $logFile;
        $logFiles[$i]['content'] = formatLog(file_get_contents($logFile));
        ++$i;
    }

    $page = $twig->render('index.html.twig', [
        'logDir' => $logDir,
        'logFiles' => $logFiles,
    ]);

    file_put_contents('index.html', $page);
}

// Create the application:
$app = new Application('TodoApp', '1.0 (stable)');

// Register all commands from src/Commands directory:
foreach (glob('src'.DIRECTORY_SEPARATOR.'Command'.DIRECTORY_SEPARATOR.'*') as $command) {
    $class = 'Console\Command\\'.pathinfo($command)['filename'];
    $app->add(new $class($logger));
}

$app->run();

function formatLog($text)
{
    // Replacing new line characters:
    $html = preg_replace("/\r\n|\r|\n/", '<br/>', $text);

    $html = str_replace('[error]', '<span style="color: red;">[error]</span>', $html);
    $html = str_replace('[info]', '<span style="color: green;">[info]</span>', $html);

    return "<pre>$html</pre>";
}
