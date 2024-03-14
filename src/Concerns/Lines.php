<?php

namespace Isaacdew\LoadData\Concerns;

trait Lines
{
    protected array $lines = [
        'startingBy' => null,
        'terminatedBy' => null,
    ];

    public function linesStartingBy(string $delimiter)
    {
        $delimiter = $this->escape($delimiter);

        $this->lines['startingBy'] = "STARTING BY {$delimiter}";

        return $this;
    }

    public function linesTerminatedBy(string $delimiter)
    {

        $delimiter = $this->escape($delimiter);
        $this->lines['terminatedBy'] = "TERMINATED BY {$delimiter}";

        return $this;
    }

    protected function buildLines()
    {
        $lines = trim(implode(' ', $this->lines));
        if (empty($lines)) {
            return null;
        }

        return ' LINES '.$lines;
    }
}
