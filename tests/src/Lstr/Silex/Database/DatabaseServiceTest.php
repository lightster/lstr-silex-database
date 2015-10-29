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

    /**
     * @dataProvider dbProvider
     */
    public function testASimpleQueryCanBeRan($db)
    {
        $sql = <<<SQL
SELECT 1 AS col UNION
SELECT 2 AS col UNION
SELECT 3
SQL;

        $result = $db->query($sql);
        $count = 1;
        while ($row = $result->fetch()) {
            $this->assertEquals($count, $row['col']);
            ++$count;
        }
    }

    public function dbProvider()
    {
        $app = new Application();
        $db = new DatabaseService($app, $this->getConfig());

        return [
            [$db],
        ];
    }

    private function getConfig()
    {
        $config = require __DIR__ . '/../../../../config/config.php';

        return $config['database'];
    }
}
