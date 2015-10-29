<?php

namespace Lstr\Silex\Database;

use PHPUnit_Framework_TestCase;
use Silex\Application;

class DatabaseServiceTest extends PHPUnit_Framework_TestCase
{
    public function testPdoConnectionCanBeRetrieved()
    {
        $app = new Application();
        $db = new DatabaseService($app, $this->getConfig());

        $pdo = $db->getPdo();
        $this->assertInstanceOf('PDO', $pdo);
        $this->assertSame($pdo, $db->getPdo());
    }

    private function getConfig()
    {
        $config = require __DIR__ . '/../../../../config/config.php';

        return $config['database'];
    }
}
