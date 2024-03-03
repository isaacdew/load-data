<?php

namespace Isaacdew\LoadData\Concerns;

use Isaacdew\LoadData\LoadDataException;

trait SetStatements
{
    protected $sets = [];

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

    protected function buildSetStatements()
    {
        if (empty($this->sets)) {
            return null;
        }
        // Throw error if sets is set but columns are not
        if (empty($this->columns)) {
            throw new LoadDataException('Columns must be defined to use the set feature.');
        }

        $sets = [];
        foreach ($this->sets as $column => $expression) {
            $sets[] = "{$column} = {$expression}";
        }

        return ' SET '.implode(', ', $sets);
    }
}
