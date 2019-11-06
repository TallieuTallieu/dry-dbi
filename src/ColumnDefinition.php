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
     * @var int $length
     */
    private $length;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var bool $null
     */
    private $null;

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
    public function rename(string $name, string $type, int $length = null)
    {
        $this->newName = $name;
        $this->type = $type;

        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    /**
     * @param int $length
     * @return $this
     */
    public function length(int $length)
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

        if ($this->isAlter) {
            if ($this->null === true) {
                $statement[] = 'NULL';
            } else if($this->null === false) {
                $statement[] = 'NOT NULL';
            }
        } else {
            $statement[] = ($this->null ? 'NULL' : 'NOT NULL');
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