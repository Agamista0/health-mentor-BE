<?php

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\MySqlConnection;

class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection(
                $connection, 
                $database, 
                $prefix, 
                $config
            );
        });

        // Add macros to Connection class
        Connection::macro('isUniqueConstraintError', function ($exception) {
            return !empty($exception->errorInfo[1]) && $exception->errorInfo[1] === 1062;
        });

        Connection::macro('isTableExistsError', function ($exception) {
            return !empty($exception->errorInfo[1]) && $exception->errorInfo[1] === 1050;
        });
    }
}
