# Introduction

MySQL and MariaDB come equipped with the [LOAD DATA INFILE](https://mariadb.com/kb/en/load-data-infile/) statement which allows for loading large datasets from a CSV or similar file into a table very quickly. This package provides an API for constructing and executing a `LOAD DATA INFILE` statement in Laravel.

# Installation

Install this package using composer:

`composer require isaacdew/load-data`

# Examples

## Basic Example

```php
use Isaacdew\LoadData\LoadData;

LoadData::from(storage_path('path/to/file.csv'))
    ->to('tablename')
    ->fieldsTerminatedBy(',')
    ->fieldsEnclosedBy('"', optionally: true)
    ->load();
```

## Ignoring the CSV Header

```php
use Isaacdew\LoadData\LoadData;

LoadData::from(storage_path('path/to/file.csv'))
    ->to('tablename')
    ->ignoreHeader()
    ->load();
```

## Defining Columns

```php
use Isaacdew\LoadData\LoadData;

LoadData::from(storage_path('path/to/file.csv'))
    ->to('tablename')
    ->fieldsTerminatedBy(',')
    ->fieldsEnclosedBy('"', optionally: true)
    ->columns([
        'column_one',
        'column_two',
        'column_three'
    ])
    ->load();
```


## Using the Headers from the CSV to Define Your Columns


```php
use Isaacdew\LoadData\LoadData;

LoadData::from(storage_path('path/to/file.csv'))
    ->to('tablename')
    ->fieldsTerminatedBy(',')
    ->fieldsEnclosedBy('"', optionally: true)
    ->useFileHeaderForColumns()
    ->load();
```

By default, the CSV headers are converted to snake case since the columns need to match your database table column names. If you need to do any custom modification, you can pass a callback to the `useFileHeaderForColumns` method.

```php
use Isaacdew\LoadData\LoadData;

LoadData::from(storage_path('path/to/file.csv'))
    ->to('tablename')
    ->fieldsTerminatedBy(',')
    ->fieldsEnclosedBy('"', optionally: true)
    // Remove parenthesis from column names
    ->useFileHeaderForColumns(fn($column) => preg_replace('/(\(.*)$/', '', $column))
    ->load();
```

## Only Loading Specific Columns from the CSV

```php
use Isaacdew\LoadData\LoadData;

LoadData::from(storage_path('path/to/file.csv'))
    ->to('tablename')
    ->fieldsTerminatedBy(',')
    ->fieldsEnclosedBy('"', optionally: true)
    ->useFileHeaderForColumns()
    ->onlyColumns([
        'column_one',
        'column_two'
    ])
    ->load();
```

## Truncating the Table Before Load

```php
use Isaacdew\LoadData\LoadData;

LoadData::from(storage_path('path/to/file.csv'))
    ->to('tablename')
    ->truncateBeforeLoad()
    ->load();
```
## Using Set Statements

To use this feature, you must define the columns first either with the `columns` method or by using the file header with the `useFileHeaderForColumns` method. Then you can modify the value from your CSV using a MySQL expression. A good use case is a date column where the CSV isn't using a MySQL friendly format. Note that you must prefix the column name with `@` to use it in your expression.

```php
use Isaacdew\LoadData\LoadData;

LoadData::from(storage_path('path/to/file.csv'))
    ->to('tablename')
    ->fieldsTerminatedBy(',')
    ->fieldsEnclosedBy('"', optionally: true)
    ->columns([
        'column_one',
        'column_two',
        'column_three',
        'date_column'
    ])
    ->setColumn('date_column', "STR_TO_DATE(@date_column, '%c/%d/%Y')") // Convert MM/DD/YYYY to a MySQL date
    ->load();
```

# Dedicated Database Servers

If your Laravel application is not on the same server as your database, you will have to make sure the [LOAD DATA LOCAL INFILE](https://mariadb.com/kb/en/load-data-infile/#load-data-local-infile) statement is enabled on your database server and in PDO. This package will automatically use the `LOCAL` keyword if your `DB_HOST` is not set to `127.0.0.1` or `localhost`.

To enable `LOAD DATA LOCAL INFILE` in PDO, go to your `config/database.php` file and add `PDO::MYSQL_ATTR_LOCAL_INFILE => true` to the array of options for the `mysql` connection like so:

```php
return [
    'connections' => [
        //...
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                PDO::MYSQL_ATTR_LOCAL_INFILE => true // Add this line!
            ]) : [],
        ],
        //...
    ]
];
```

# Note On Security

Prepared statements are not supported for `LOAD DATA INFILE` statements. With that being the case, **do not use user input for constructing a `LOAD DATA INFILE` statement**. I took the precaution of using `PDO::quote()` to escape the filename, however, I would still recommend against the use of a user provided filename in this statement.
