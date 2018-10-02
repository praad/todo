<?php

namespace Test;

use Model\Todo;
use Katzgrau\KLogger\Logger;
use PHPUnit\Framework\TestCase;

define('TESTLOGDIR', 'testlog');

/**
 * @testdox Todo
 */
class TodoTest extends TestCase
{
    /**
     * Constructor of the Test class.
     *
     * @param [type] $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        // Check if the TESTLOGDIR is exists or not and create it if neccessary:
        if (!is_dir(TESTLOGDIR)) {
            mkdir(TESTLOGDIR);
        }
    }

    /**
     * @testdox Can TODO be created without logger
     */
    public function testCanBeCreatedWithoutLogger(): void
    {
        $todo = new Todo();
        $todo->setStatus(strtoupper('NEW'));
        $todo->description = 'Test';
        $todo->save();

        $this->assertInstanceOf(
            Todo::class,
            $todo
        );

        $todo->delete();
    }

    /**
     * @testdox Can TODO be created with logger
     */
    public function testCanBeCreatedWithLogger(): void
    {
        $logger = new Logger(TESTLOGDIR, \Psr\Log\LogLevel::INFO, [
            'extension' => 'log',
        ]);
        $todo = new Todo();
        $todo->setStatus(strtoupper('NEW'));
        $todo->setLogger($logger);
        $todo->description = 'Test';
        $todo->save();

        $this->assertInstanceOf(
            Todo::class,
            $todo
        );

        $todo->delete();
    }

    /**
     * Desctructor of the Test class
     * Remove test logs.
     */
    public function __destruct()
    {
        // Delete all test log
        if (is_dir(TESTLOGDIR)) {
            foreach (glob(TESTLOGDIR.DIRECTORY_SEPARATOR.'*') as $file) {
                unlink($file);
            }
            rmdir(TESTLOGDIR);
        }
    }
}
