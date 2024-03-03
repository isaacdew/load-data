<?php

namespace Isaacdew\LoadData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LoadData
{
    protected string $file;

    protected bool $ignoreErrors = false;

    protected string $table;

    protected $characterSet;

    protected array $fields = [
        'terminatedBy' => null,
        'enclosedBy' => null,
        'escapedBy' => null,
    ];

    protected array $lines = [
        'startingBy' => null,
        'terminatedBy' => null,
    ];

    protected $separator = ',';

    protected $enclosure = '"';

    protected $escape = '\\';

    protected $ignoreLines;

    protected $columns = [];

    protected $onlyColumns = [];

    protected $sets = [];

    public function __construct($file)
    {
        $this->file = $file instanceof File
            ? $file->path()
            : $file;
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

    protected function useLocal()
    {
        $host = env('DB_HOST');

        return $host !== '127.0.0.1' && $host !== 'localhost';
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

    public function fieldsTerminatedBy(string $separator)
    {
        $this->separator = $separator;
        $this->fields['terminatedBy'] = "TERMINATED BY '{$separator}'";

        return $this;
    }

    public function fieldsEnclosedBy(string $enclosure, bool $optionally = false)
    {
        $this->enclosure = $enclosure;
        $this->fields['enclosedBy'] = "ENCLOSED BY '{$enclosure}'";

        if ($optionally) {
            $this->fields['enclosedBy'] = 'OPTIONALLY '.$this->fields['enclosedBy'];
        }

        return $this;
    }

    public function fieldsEscapedBy(string $escape)
    {
        $this->escape = $escape;
        $this->fields['escapedBy'] = "ESCAPED BY '{$escape}'";

        return $this;
    }

    public function linesStartingBy(string $delimiter)
    {
        $this->lines['startingBy'] = "STARTING BY '{$delimiter}'";

        return $this;
    }

    public function linesTerminatedBy(string $delimiter)
    {
        $this->lines['terminatedBy'] = "TERMINATED BY '{$delimiter}'";

        return $this;
    }

    public function columns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function onlyLoadColumns(array $columns)
    {
        $this->onlyColumns = $columns;

        return $this;
    }

    public function setColumns(array $sets)
    {
        $this->sets = array_merge($this->sets, $sets);

        return $this;
    }

    public function setColumn($key, $expression)
    {
        $this->sets[$key] = $expression;

        return $this;
    }

    public function useFileHeaderForColumns()
    {
        // Load the first line into memory
        $file = fopen($this->file, 'r');
        $firstLine = fgetcsv($file, null, $this->separator, $this->enclosure, $this->escape);
        fclose($file);

        // Make the headings DB friendly
        $this->columns = array_map(function ($heading) {
            // Remove anything in parenthesis
            $heading = preg_replace('/(\(.*)$/', '', $heading);

            return str()->snake(strtolower($heading));
        }, $firstLine);

        $this->ignoreHeader();

        return $this;
    }

    public function load()
    {
        return DB::statement($this->toSql());
    }

    public function toSql()
    {
        // Determine if we need the "LOCAL" keyword
        $local = $this->useLocal() ? ' LOCAL' : '';

        $sql = "LOAD DATA{$local} INFILE '{$this->file}'";

        if ($this->ignoreErrors) {
            $sql .= ' IGNORE';
        }

        $sql .= " INTO TABLE {$this->table}";

        if ($this->characterSet) {
            $sql .= ' '.$this->characterSet;
        }

        $fields = trim(implode(' ', $this->fields));
        if (! empty($fields)) {
            $sql .= ' FIELDS '.$fields;
        }

        $lines = trim(implode(' ', $this->lines));
        if (! empty($lines)) {
            $sql .= ' LINES '.$lines;
        }

        if ($this->ignoreLines) {
            $sql .= ' '.$this->ignoreLines;
        }

        // Build columns
        if (! empty($this->columns)) {
            $onlyColumns = ! empty($this->onlyColumns)
                ? array_flip($this->onlyColumns)
                : false;

            $columns = [];
            foreach ($this->columns as $column) {
                // Skip columns if not included in the only list, if it exists
                if ($onlyColumns && ! isset($onlyColumns[$column])) {
                    $columns[] = '@dummy';

                    continue;
                }

                // Set column as variable
                if (isset($this->sets[$column])) {
                    $columns[] = '@'.$column;

                    continue;
                }

                $columns[] = $column;
            }

            $sql .= ' ('.implode(', ', $columns).')';
        }

        // Build set statements
        if (! empty($this->sets)) {
            // Throw error if sets is set but columns are not
            if (empty($this->columns)) {
                // TODO: Use custom exception
                throw new InvalidArgumentException('Columns must be defined to use the set feature.');
            }

            $sets = [];
            foreach ($this->sets as $column => $expression) {
                $sets[] = "{$column} = {$expression}";
            }

            $sql .= ' SET '.implode(', ', $sets);
        }

        return $sql;
    }
}
