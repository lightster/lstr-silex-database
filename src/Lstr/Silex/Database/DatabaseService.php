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

use Pdo;

use Silex\Application;

class DatabaseService
{
    private $app;
    private $config;

    private $pdo;

    public function __construct(Application $app, array $config)
    {
        $this->app    = $app;
        $this->config = $config;
    }

    public function getPdo()
    {
        if (null !== $this->pdo) {
            return $this->pdo;
        }

        $dsn      = $this->config['dsn'];
        $username = isset($this->config['username']) ? $this->config['username'] : null;
        $password = isset($this->config['password']) ? $this->config['password'] : null;
        $options  = isset($this->config['driver_options']) ? $this->config['driver_options'] : null;

        $this->pdo = new PDO($dsn, $username, $password, $options);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $this->pdo;
    }

    public function query($sql, array $params = array())
    {
        return $this->queryWithOptions($sql, $params);
    }

    public function queryMultiple($sql, array $params = array())
    {
        // turn on query emulation so multiple queries can be ran
        $options = array(
            PDO::ATTR_EMULATE_PREPARES => true,
        );

        return $this->queryWithOptions($sql, $params, $options);
    }

    public function getLastInsertId($sequence_table = null)
    {
        return $this->getPdo()->lastInsertId($sequence_table);
    }

    public function insert($tablename, array $values)
    {
        $columns      = array();
        $placeholders = array();
        foreach ($values as $column => $value) {
            $columns[]      = $column;
            $placeholders[] = ":{$column}";
        }

        $column_sql      = implode(",\n", $columns);
        $placeholder_sql = implode(",\n", $placeholders);

        $this->query(
            "
                INSERT INTO {$tablename}
                (
                    {$column_sql}
                )
                VALUES
                (
                    {$placeholder_sql}
                )
            ",
            $values
        );
    }

    public function update($tablename, array $set_cols, $where_sql, array $values)
    {
        $sets = array();
        foreach ($set_cols as $column => $placeholder) {
            if (is_numeric($column)) {
                $column = $placeholder;
            }
            $sets[] = "{$column} = :{$placeholder}";
        }

        $set_sql = implode(",\n", $sets);

        $this->query(
            "
                UPDATE {$tablename}
                SET {$set_sql}
                WHERE {$where_sql}
            ",
            $values
        );
    }

    private function queryWithOptions($sql, array $params = array(), array $options = array())
    {
        $pdo   = $this->getPdo();
        $query = $pdo->prepare($sql, $options);
        $query->execute($params);

        return $query;
    }
}
