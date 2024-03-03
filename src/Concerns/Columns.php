<?php

namespace Isaacdew\LoadData\Concerns;

trait Columns
{
    protected $columns = [];

    protected $onlyColumns = [];

    public function columns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function onlyLoadColumns(array $columns)
    {
        $this->onlyColumns = $columns;

        return $this;
    }

    public function useFileHeaderForColumns(?callable $callback = null)
    {
        // Load the first line into memory
        $file = fopen($this->file, 'r');
        $firstLine = fgetcsv($file, null, $this->separator, $this->enclosure, $this->escape);
        fclose($file);

        // Make the headings DB friendly
        $callback ??= function ($heading) {
            return str()->snake(strtolower($heading));
        };

        $this->columns = array_map($callback, $firstLine);

        $this->ignoreHeader();

        return $this;
    }

    protected function buildColumns()
    {
        if (empty($this->columns)) {
            return null;
        }

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

        return ' ('.implode(', ', $columns).')';
    }
}
