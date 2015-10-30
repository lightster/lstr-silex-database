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
    private $app;
    private $config;

    private $yo_pdo;

    public function __construct(Application $app, array $config)
    {
        $this->app    = $app;
        $this->config = $config;
    }

    public function getPdo()
    {
        return $this->getYoPdo()->getPdo();
    }

    public function query($sql, array $params = array())
    {
        return $this->getYoPdo()->query($sql, $params);
    }

    public function queryMultiple($sql, array $params = array())
    {
        return $this->getYoPdo()->queryMultiple($sql, $params);
    }

    public function getLastInsertId($sequence_table = null)
    {
        return $this->getYoPdo()->getLastInsertId($sequence_table);
    }

    public function insert($tablename, array $values)
    {
        return $this->getYoPdo()->insert($tablename, $values);
    }

    public function update($tablename, array $set_cols, $where_sql, array $values)
    {
        return $this->getYoPdo()->update($tablename, $set_cols, $where_sql, $values);
    }

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
