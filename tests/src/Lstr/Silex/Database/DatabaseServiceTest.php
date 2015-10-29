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
SELECT :param_a AS col UNION
SELECT :param_b AS col UNION
SELECT :last_param AS col
ORDER BY col
SQL;

        $result = $db->query($sql, [
            'param_a'    => 1,
            'param_b'    => 2,
            'last_param' => 3,
        ]);
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
