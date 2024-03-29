<?php

namespace Tests;

use PDO;

use function Orchestra\Testbench\workbench_path;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');

        $app['config']->set('database.default', 'mysql');

        $app['config']->set('database.connections.mysql.options', [
            PDO::MYSQL_ATTR_LOCAL_INFILE => true,
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(workbench_path('database/migrations'));
    }

    protected function assertStringEqualsIgnoringSlashes(string $expected, string $actual)
    {
        // Ignore slashes
        $expected = trim(json_encode(
            $expected,
            JSON_UNESCAPED_SLASHES
        ), '"');

        $message = "Failed asserting that two strings are equal.
        --- Expected
        +++ Actual
        @@ @@
        -'{$expected}'
        +'{$actual}'";

        $this->assertTrue(
            $expected === $actual,
            $message
        );
    }
}
