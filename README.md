# Introduction

MySQL and MariaDB come equipped with the [LOAD DATA INFILE](https://mariadb.com/kb/en/load-data-infile/) statement which allows for loading large datasets from a CSV or similar file into a table very quickly. This package provides an API for constructing and executing a LOAD DATA INFILE statement in Laravel.

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

To use this feature, you must define the columns first. Then you can modify the value from your CSV using a MySQL expression. A good use case is date column where the CSV isn't using a MySQL friendly format. Note that you must prefix the column name with `@` to use it in your expression.

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
        'date_column'
    ])
    ->setColumn('date_column', "STR_TO_DATE(@date_column, '%c/%d/%Y')") // Convert MM/DD/YYYY to a MySQL date
    ->load();
```

# Note On Security

Prepared statements are not supported for `LOAD DATA INFILE` statements. With that being the case, **do not use user input for constructing a `LOAD DATA INFILE` statement**. I took the precaution of using `PDO::quote()` to escape the filename, however, I would still recommend against the use of a user provided filename in this statement.
