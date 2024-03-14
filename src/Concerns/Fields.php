<?php

namespace Isaacdew\LoadData\Concerns;

trait Fields
{
    protected array $fields = [
        'terminatedBy' => null,
        'enclosedBy' => null,
        'escapedBy' => null,
    ];

    protected $separator = ',';

    protected $enclosure = '"';

    protected $escape = '\\';

    public function fieldsTerminatedBy(string $separator)
    {
        $this->separator = $separator;

        $separator = $this->escape($separator);
        $this->fields['terminatedBy'] = "TERMINATED BY {$separator}";

        return $this;
    }

    public function fieldsEnclosedBy(string $enclosure, bool $optionally = false)
    {
        $this->enclosure = $enclosure;

        $enclosure = $this->escape($enclosure);
        $this->fields['enclosedBy'] = "ENCLOSED BY {$enclosure}";

        if ($optionally) {
            $this->fields['enclosedBy'] = 'OPTIONALLY '.$this->fields['enclosedBy'];
        }

        return $this;
    }

    public function fieldsEscapedBy(string $escape)
    {
        $this->escape = $escape;

        $escape = $this->escape($escape);
        $this->fields['escapedBy'] = "ESCAPED BY {$escape}";

        return $this;
    }

    protected function buildFields()
    {
        $fields = trim(implode(' ', $this->fields));
        if (empty($fields)) {
            return null;
        }

        return ' FIELDS '.$fields;
    }
}
