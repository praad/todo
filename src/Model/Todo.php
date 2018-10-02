<?php
/**
 * Todo model class.
 */

namespace Model;

use Katzgrau\KLogger\Logger;

class Todo
{
    private $id;

    public $dataDir;

    public $valid = false;

    protected $logger;

    public function __construct()
    {
        // Check if the DATADIR is exists or not and create it if neccessary:
        $this->dataDir = (getenv('TODODIR')) ?: 'todo';
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir);
        }
    }

    /**
     * $legalStatus values.
     *
     * @var array
     */
    public static $legalStatus = ['NEW', 'INPROGRESS', 'ONHOLD', 'DONE'];

    /**
     * $tatus the status of the Todo.
     *
     * @var string NEW|INPROGRESS|ONHOLD|DONE
     */
    private $status = 'NEW';

    /**
     * $creationDate the creation date of the Todo in the given format 2018-09-21 18:00:00.
     *
     * @var string
     */
    private $creationDate;

    /**
     * Set logger.
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Save the todo.
     */
    public function save()
    {
        if ($this->valid) {
            $this->setCreationDate(date('Y-m-d H:i:s'));

            $filename = $this->getLastId() + 1;

            $this->setId($filename);

            // Serialize the todo object
            $todo = serialize($this);

            file_put_contents($this->dataDir.DIRECTORY_SEPARATOR.$filename, $todo);

            $this->setId($filename);

            //$this->list();
            echo "The todo is saved successfully. \n";

            if ($this->logger) {
                $this->logger->info($this->getId().' todo saved successfully.');
            }

            return true;
        }
        echo "The todo can not be saved. \n";

        if ($this->logger) {
            $this->logger->error('Todo can not be saved.');
        }

        return false;
    }

    /**
     * List the todo.
     */
    public function list()
    {
        echo "TODO: \n";
        echo 'Id: '.$this->getId()."\n";
        echo 'Status: '.$this->getStatus()."\n";
        echo "Description: $this->description \n";
        echo 'Date: '.$this->getCreationDate()."\n";
    }

    /**
     * Get $creationDate the creation date of the Todo in the given format 2018-09-21 18:00:00.
     *
     * @return string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set $creationDate the creation date of the Todo in the given format 2018-09-21 18:00:00.
     *
     * @param string $creationDate the creation date of the Todo in the given format 2018-09-21 18:00:00
     *
     * @return self
     */
    public function setCreationDate($creationDate)
    {
        // Set the creation date for now()
        $this->creationDate = date('Y-m-d H:i:s');

        if ($creationDate) {
            $this->creationDate = $creationDate;
        }

        return $this;
    }

    /**
     * Get nEW|INPROGRESS|ONHOLD|DONE.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set NEW|INPROGRESS|ONHOLD|DONE.
     *
     * @param string $status in NEW|INPROGRESS|ONHOLD|DONE
     *
     * @return self
     */
    public function setStatus(string $status)
    {
        if (in_array($status, self::$legalStatus)) {
            $this->status = $status;
            $this->valid = true;

            return $this;
        }

        echo 'Bad status value use from this list: '.
            implode('|', self::$legalStatus)."\n";

        $this->valid = false;

        return false;
    }

    /**
     * List all todos.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD)
     */
    public static function all(Logger $logger = null)
    {
        $todos = self::loadALl();

        if (empty($todos)) {
            echo "No todo available yet... \n";

            if ($logger) {
                $logger->warning('No available todos.');
            }

            return false;
        }

        /*
        foreach ($todos as $todo) {
            $todo->list();
        }
        */

        if ($logger) {
            $logger->info('Get all todos.');
        }

        return $todos;
    }

    /**
     * Find todo by id.
     *
     * @return mixed
     */
    public static function find($id = null, Logger $logger = null)
    {
        $todo = self::loadAll($id);

        if ($todo) {
            $todo->list();

            if ($logger) {
                $logger->info("Get $id. todo.");
            }

            return $todo;
        }

        echo "$id. todo. not exists. \n";
        if ($logger) {
            $logger->warning("$id. todo not exists.");
        }

        return false;
    }

    /**
     * Setter for id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get all todo ids.
     *
     * @return array of Todo
     */
    private function getLastId()
    {
        $todos = [];
        foreach (glob($this->dataDir.DIRECTORY_SEPARATOR.'*') as $todo) {
            $todo = unserialize(file_get_contents($todo));
            $todos[] = $todo->id;
        }

        asort($todos);

        return end($todos);
    }

    /**
     * Load all todos.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD)
     */
    private function loadAll($id = null)
    {
        $selectedTodo = null;

        $dataDir = (getenv('TODODIR')) ?: 'todo';

        foreach (glob($dataDir.DIRECTORY_SEPARATOR.'*') as $file) {
            $todo = unserialize(file_get_contents($file));
            if ($id) {
                if ($todo->id == $id) {
                    $selectedTodo = $todo;
                }
            }
            $todos[] = $todo;
        }

        if ($id) {
            if ($selectedTodo) {
                return $selectedTodo;
            }

            return null;
        }

        usort($todos, function ($valueA, $valueB) {
            return $valueA->id - $valueB->id;
        });

        return $todos;
    }

    /**
     * Update and validate the status value.
     *
     * @param int    $id     of Todo
     * @param string $status in NEW|INPROGRESS|ONHOLD|DONE
     *
     * @SuppressWarnings(PHPMD)
     */
    public function updateStatus($status, $description = null, Logger $logger = null)
    {
        // Validate the update of the status:
        $validator = require 'Validator.php';
        $oldStatus = $this->getStatus();

        if (in_array($status, $validator[$oldStatus])) {
            $this->setStatus($status);

            if ($description) {
                $this->description = $description;
            }

            $todo = serialize($this);

            file_put_contents($this->dataDir.'/'.$this->getId(), $todo);

            echo $this->getId()." todo updated with status: $oldStatus -> $status. \n";

            if ($logger) {
                $logger->info($this->getId()." todo updated with status: $oldStatus -> $status, $this->description.");
            }

            return true;
        }

        echo $this->getId()." todo not valid change of the status: $oldStatus -> $status, $this->description.\n";

        if ($logger) {
            $logger->error($this->getId()." todo not valid change of the status: 
                $oldStatus -> $status, $this->description");
        }

        return false;
    }

    /**
     * Delete the todo.
     */
    public function delete()
    {
        if (unlink($this->dataDir.DIRECTORY_SEPARATOR.$this->getId())) {
            return true;
        }

        return false;
    }
}
