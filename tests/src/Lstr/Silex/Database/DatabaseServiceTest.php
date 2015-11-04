<?php

namespace Lstr\Silex\Database;

use PDOException;
use PHPUnit_Framework_TestCase;
use Silex\Application;

class DatabaseServiceTest extends PHPUnit_Framework_TestCase
{
    public function testPdoConnectionCanBeRetrieved()
    {
        $config = $this->getConfig();
        $db_service = new DatabaseService(new Application, $this->getConfig());

        $pdo = $db_service->getPdo();
        $this->assertInstanceOf('PDO', $pdo);
        $this->assertSame($pdo, $db_service->getPdo());
    }

    /**
     * @dataProvider dbProvider
     * @expectedException PDOException
     */
    public function testAnErrorInAQueryThrowsAnException($db_service)
    {
        $db_service->query('SELECT oops');
    }

    /**
     * @dataProvider dbProvider
     */
    public function testASimpleQueryCanBeRan($db_service)
    {
        $sql = <<<SQL
SELECT :param_a AS col UNION
SELECT :param_b AS col UNION
SELECT :last_param AS col
ORDER BY col
SQL;

        $result = $db_service->query($sql, array(
            'param_a'    => 1,
            'param_b'    => 2,
            'last_param' => 3,
        ));
        $count = 1;
        while ($row = $result->fetch()) {
            $this->assertEquals($count, $row['col']);
            ++$count;
        }
    }

    /**
     * @dataProvider dbProvider
     */
    public function testMultipleQueriesCanBeRan($db_service)
    {
        $table_name = $this->createTable($db_service);
        $sql = <<<SQL
INSERT INTO {$table_name} (a, b) VALUES (:row_1_col_a, :row_1_col_b);
INSERT INTO {$table_name} (a, b) VALUES (:row_2_col_a, :row_2_col_b);
INSERT INTO {$table_name} (a, b) VALUES (:last_row_col_a, :last_row_col_b);
SQL;

        $params = array(
            'row_1_col_a'    => 20,
            'row_1_col_b'    => 40,
            'row_2_col_a'    => 60,
            'row_2_col_b'    => 30,
            'last_row_col_a' => 50,
            'last_row_col_b' => 10,
        );
        $db_service->queryMultiple($sql, $params);

        $this->assertResults($db_service, $table_name, array(
            1 => array('a' => $params['row_1_col_a'], 'b' => $params['row_1_col_b']),
            2 => array('a' => $params['row_2_col_a'],'b' => $params['row_2_col_b']),
            3 => array('a' => $params['last_row_col_a'], 'b' => $params['last_row_col_b']),
        ));
    }

    /**
     * @dataProvider dbProvider
     */
    public function testInsert($db_service)
    {
        $rows = $this->getSampleRows();

        $table_name = $this->createTable($db_service);
        foreach ($rows as $row) {
            $db_service->insert($table_name, $row);
        }

        $this->assertResults($db_service, $table_name, $rows);
    }

    /**
     * @dataProvider dbProvider
     */
    public function testLastInsertIdCanBeRetrieved($db_service)
    {
        $table_name = $this->createTable($db_service);
        for ($i = 1; $i <= 3; $i++) {
            $db_service->insert($table_name, array('a' => $i + 5, 'b' => $i + 10));
            $this->assertEquals($i, $db_service->getLastInsertId("{$table_name}_id_seq"));
        }
    }

    /**
     * @dataProvider dbProvider
     */
    public function testUpdateRecordsUsingCustomPlaceholderNames($db_service)
    {
        $this->assertUpdated(
            $db_service,
            function ($table_name, $condition) use ($db_service) {
                $db_service->update(
                    $table_name,
                    array('a' => 'some_number', 'b' => 'some_number'),
                    $condition,
                    array('some_number' => 112)
                );

                return array('a' => 112, 'b' => 112);
            }
        );
    }

    /**
     * @dataProvider dbProvider
     */
    public function testUpdateRecordsUsingColumnNamesAsPlaceholderNames($db_service)
    {
        $this->assertUpdated(
            $db_service,
            function ($table_name, $condition) use ($db_service) {
                $expected = array('a' => 102, 'b' => 120);

                $db_service->update(
                    $table_name,
                    array('a', 'b'),
                    $condition,
                    $expected
                );

                return $expected;
            }
        );
    }

    /**
     * @return array
     */
    public function dbProvider()
    {
        return array(
            array(new DatabaseService(new Application, $this->getConfig())),
        );
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        $config = require __DIR__ . '/../../../../config/config.php';

        return $config['database'];
    }

    /**
     * @param DatabaseService $db_service
     * @return string
     */
    private function createTable(DatabaseService $db_service)
    {
        $table_name = 'test_' . uniqid();
        $sql = <<<SQL
CREATE SEQUENCE {$table_name}_id_seq;
CREATE TABLE {$table_name} (
    id INT NOT NULL PRIMARY KEY DEFAULT NEXTVAL('{$table_name}_id_seq'::regclass),
    a INT NOT NULL,
    b INT NOT NULL
);
SQL;
        $db_service->queryMultiple($sql);

        return $table_name;
    }

    /**
     * @param DatabaseService $db_service
     * @param $table_name
     * @param array $rows
     */
    private function populateTable(DatabaseService $db_service, $table_name, array $rows)
    {
        foreach ($rows as $row) {
            $db_service->insert($table_name, $row);
        }
    }

    /**
     * @param DatabaseService $db_service
     * @param array $rows
     * @return string
     */
    private function createPopulatedTable(DatabaseService $db_service, array $rows)
    {
        $table_name = $this->createTable($db_service);
        $this->populateTable($db_service, $table_name, $rows);

        return $table_name;
    }

    /**
     * @param DatabaseService $db_service
     * @param callable $run_update
     */
    private function assertUpdated(DatabaseService $db_service, $run_update)
    {
        $rows = $this->getSampleRows();
        $table_name = $this->createPopulatedTable($db_service, $rows);

        $expected = $rows;
        $expected[2] = $run_update($table_name, 'id = 2');

        $this->assertResults($db_service, $table_name, $expected);
    }

    /**
     * @param DatabaseService $db_service
     * @param string $table_name
     * @param array $expected_results
     * @return array
     */
    private function assertResults(DatabaseService $db_service, $table_name, array $expected_results)
    {
        $sql = <<<SQL
SELECT id, a, b
FROM {$table_name}
ORDER BY id
SQL;
        $result = $db_service->query($sql);
        while ($row = $result->fetch()) {
            if (!array_key_exists('id', $row)) {
                $this->assertTrue(false, "Field 'id' not found in row.");
            } else if (!array_key_exists($row['id'], $expected_results)) {
                $this->assertTrue(false, "Row with key '{$row['id']}' not found in expected results.");
            } else {
                $expected_result = $expected_results[$row['id']];
                $expected_result['id'] = $row['id'];
                $this->assertEquals($expected_result, $row);
            }
        }
    }

    /**
     * @return array
     */
    private function getSampleRows()
    {
        return array(
            1 => array('a' => 3, 'b' => 6),
            2 => array('a' => 2, 'b' => 4),
            3 => array('a' => 1, 'b' => 2),
        );
    }
}
