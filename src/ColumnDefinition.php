<?php

namespace Tnt\Dbi;

class ColumnDefinition
{
    /**
     * @var bool $isAlter
     */
    private $isAlter;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var $newName
     */
    private $newName;

    /**
     * @var mixed $length
     */
    private $length;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $generate
     */
    private $generateQuery;

    /**
     * @var bool $null
     */
    private $null;

    /**
    * @var $defaultVal
    */
    private $defaultVal = false;

    /**
     * @var bool $autoIncrement
     */
    private $autoIncrement = false;

    /**
     * @var bool $primaryKey
     */
    private $primaryKey = false;

    /**
     * ColumnDefinition constructor.
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, bool $isAlter = false)
    {
        $this->name = $name;
        $this->isAlter = $isAlter;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function type(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param int|null $length
     * @return $this
     */
    public function rename(string $name, string $type, ?int $length = null)
    {
        $this->newName = $name;
        $this->type = $type;

        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    /**
     * @param mixed $length
     * @return $this
     */
    public function length($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return $this
     */
    public function primaryKey()
    {
        $this->autoIncrement = true;
        $this->primaryKey = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function null()
    {
        $this->null = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function notNull()
    {
        $this->null = false;
        return $this;
    }

    public function default($defaultVal)
    {
        $this->defaultVal = $defaultVal;
        return $this;
    }

    public function generate($query)
    {
        $this->generateQuery = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        $statement = [];
        $statement[] = '`'.$this->name.'`'.($this->isAlter ? ' `'.($this->newName ?: $this->name).'`' : '');

        if ($this->type) {

            $type = strtoupper($this->type);

            if ($this->length) {
                $type .= '('.$this->length.')';
            }

            $statement[] = $type;
        }

        if ($this->generateQuery) {
            $gerateStatement = 'GENERATED ALWAYS as (' . $this->generateQuery . ')';

            $statement[] = $gerateStatement;
            return implode(' ', $statement);
        }

        $statement[] = ($this->null ? 'NULL' : 'NOT NULL');

        if ($this->defaultVal !== false) {
            if (is_string($this->defaultVal)) {
                $statement[] = "DEFAULT '" . addslashes($this->defaultVal) . "'";
            } else if (is_null($this->defaultVal)) {
                $statement[] = "DEFAULT NULL";
            }
            else {
                $statement[] = "DEFAULT " . $this->defaultVal;
            }
        }

        if ($this->autoIncrement) {
            $statement[] = 'AUTO_INCREMENT';
        }

        if ($this->primaryKey) {
            $statement[] = 'PRIMARY KEY';
        }

        return implode(' ', $statement);
    }
}

