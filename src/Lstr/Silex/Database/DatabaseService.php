<?php
/*
 * Lstr/Silex source code
 *
 * Copyright Matt Light <matt.light@lightdatasys.com>
 *
 * For copyright and licensing information, please view the LICENSE
 * that is distributed with this source code.
 */

namespace Lstr\Silex\Database;

use Lstr\YoPdo\Factory as YoPdoFactory;
use Silex\Application;

class DatabaseService
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var YoPdo
     */
    private $yo_pdo;

    /**
     * @param Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app    = $app;
        $this->config = $config;
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->getYoPdo()->getPdo();
    }

    /**
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, array $params = array())
    {
        return $this->getYoPdo()->query($sql, $params);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return generator
     */
    public function getSelectRowGenerator($sql, array $params = array())
    {
        return $this->getYoPdo()->getSelectRowGenerator($sql, $params);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function queryMultiple($sql, array $params = array())
    {
        return $this->getYoPdo()->queryMultiple($sql, $params);
    }

    /**
     * @param string $sequence_table
     * @return string
     */
    public function getLastInsertId($sequence_table = null)
    {
        return $this->getYoPdo()->getLastInsertId($sequence_table);
    }

    /**
     * @param string $tablename
     * @param array $values
     */
    public function insert($tablename, array $values)
    {
        return $this->getYoPdo()->insert($tablename, $values);
    }

    /**
     * @param string $tablename
     * @param array $set_cols
     * @param string $where_sql
     * @param array $values
     */
    public function update($tablename, array $set_cols, $where_sql, array $values)
    {
        return $this->getYoPdo()->update($tablename, $set_cols, $where_sql, $values);
    }

    /**
     * @param string $table_name
     * @param array $columns
     * @param int $max_buffer_size
     * @return BulkInserter
     */
    public function getBulkInserter($table_name, array $columns, $max_buffer_size = 250)
    {
        return $this->getYoPdo()->getBulkInserter($table_name, $columns, $max_buffer_size);
    }

    /**
     * @return YoPdo
     */
    private function getYoPdo()
    {
        if ($this->yo_pdo) {
            return $this->yo_pdo;
        }

        $factory = new YoPdoFactory();
        $this->yo_pdo = $factory->createFromConfig($this->config);

        return $this->yo_pdo;
    }
}
