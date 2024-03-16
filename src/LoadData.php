<?php

namespace Isaacdew\LoadData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Isaacdew\LoadData\Concerns\Columns;
use Isaacdew\LoadData\Concerns\Fields;
use Isaacdew\LoadData\Concerns\Lines;
use Isaacdew\LoadData\Concerns\SetStatements;
use PDO;

class LoadData
{
    use Columns, Fields, Lines, SetStatements;

    protected PDO $pdo;

    protected string $file;

    protected bool $ignoreErrors = false;

    protected string $table;

    protected $characterSet;

    protected $ignoreLines;

    protected $truncateBeforeLoad = false;

    protected $useLocalKeyword;

    public function __construct($file)
    {
        $this->file = $file instanceof File
            ? $file->path()
            : $file;

        $this->pdo = DB::connection()->getPdo();
    }

    /**
     * @param  string|File  $file
     * @return static
     */
    public static function from($file): LoadData
    {
        return new static($file);
    }

    /**
     * @param  string|Model  $table
     */
    public function to($table): LoadData
    {
        $this->table = is_subclass_of($table, Model::class)
            ? (new $table)->getTable()
            : $table;

        return $this;
    }

    public function useLocalKeyword($useLocal = true)
    {
        $this->useLocalKeyword = $useLocal;

        return $this;
    }

    public function ignoreErrors(bool $bool = true)
    {
        $this->ignoreErrors = $bool;

        return $this;
    }

    public function ignoreLines(int $lines)
    {
        $this->ignoreLines = "IGNORE {$lines} LINES";

        return $this;
    }

    public function ignoreHeader()
    {
        $this->ignoreLines(1);

        return $this;
    }

    public function characterSet(string $characterSet)
    {
        $this->characterSet = "CHARACTER SET {$characterSet}";

        return $this;
    }

    public function truncateBeforeLoad(bool $bool = true)
    {
        $this->truncateBeforeLoad = $bool;

        return $this;
    }

    public function load()
    {
        if ($this->truncateBeforeLoad) {
            DB::table($this->table)->truncate();
        }

        return DB::statement($this->toSql());
    }

    public function toSql()
    {
        $host = env('DB_HOST');
        $this->useLocalKeyword ??= ($host !== '127.0.0.1' && $host !== 'localhost') || env('USING_DOCKER');

        // Determine if we need the "LOCAL" keyword
        $local = $this->useLocalKeyword ? ' LOCAL' : '';

        // Since prepared statements are not supported for LOAD DATA, we use the quote function to escape the filename
        $escapedFilename = DB::connection()->getPdo()->quote($this->file);
        $sql = "LOAD DATA{$local} INFILE {$escapedFilename}";

        if ($this->ignoreErrors) {
            $sql .= ' IGNORE';
        }

        $sql .= " INTO TABLE {$this->table}";

        if ($this->characterSet) {
            $sql .= ' '.$this->characterSet;
        }

        $sql .= $this->buildFields();

        $sql .= $this->buildLines();

        if ($this->ignoreLines) {
            $sql .= ' '.$this->ignoreLines;
        }

        // Build columns
        $sql .= $this->buildColumns();

        // Build set statements
        $sql .= $this->buildSetStatements();

        return $sql;
    }
}
