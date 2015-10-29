<?php

namespace Lstr\Silex\Database;

use Exception;
use PHPUnit_Framework_TestCase;
use Silex\Application;

class DatabaseServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testDatabaseServiceGeneratorIsCallable()
    {
        $app = new Application();
        $provider = new DatabaseServiceProvider();

        $provider->register($app);

        $this->assertTrue(is_callable($app['lstr.db']));
    }

    public function testDatabaseServiceGeneratorReturnsACallable()
    {
        $app = new Application();
        $provider = new DatabaseServiceProvider();

        $provider->register($app);

        $db_provider = $app['lstr.db'];
        $db_shareable = $db_provider([
            'dsn'      => 'connection_string',
            'username' => 'username',
            'password' => 'password',
        ]);
        $this->assertTrue(is_callable($db_shareable));
    }

    public function testDatabaseServiceCanBeGenerated()
    {
        $app = new Application();
        $provider = new DatabaseServiceProvider();

        $provider->register($app);

        $db_provider = $app['lstr.db'];
        $app['db_shareable'] = $db_provider([
            'dsn'      => 'connection_string',
            'username' => 'username',
            'password' => 'password',
        ]);
        $this->assertInstanceOf('\Lstr\Silex\Database\DatabaseService', $app['db_shareable']);
        $this->assertSame($app['db_shareable'], $app['db_shareable']);
    }

    public function testDatabaseServiceCanBeGeneratedWithConfigCallback()
    {
        $app = new Application();
        $provider = new DatabaseServiceProvider();

        $provider->register($app);

        $db_provider = $app['lstr.db'];
        $app['db_shareable'] = $db_provider(function () {
            return [
                'dsn'      => 'connection_string',
                'username' => 'username',
                'password' => 'password',
            ];
        });
        $this->assertInstanceOf('\Lstr\Silex\Database\DatabaseService', $app['db_shareable']);
    }

    public function testBootCanBeCalled()
    {
        $app = new Application();
        $provider = new DatabaseServiceProvider();

        $provider->boot($app);

        $this->assertTrue(true);
    }
}
