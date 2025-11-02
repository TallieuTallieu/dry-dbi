<?php

namespace Tnt\Dbi;

class ColumnDefinition
{
    /**
     * @var bool
     */
    private bool $isAlter;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string|null
     */
    private ?string $newName = null;

    /**
     * @var int|string|null
     */
    private int|string|null $length = null;

    /**
     * @var string
     */
    private string $type = '';

    /**
     * @var string|null
     */
    private ?string $generateQuery = null;

    /**
     * @var bool
     */
    private bool $null = false;

    /**
     * @var mixed
     */
    private mixed $defaultVal = false;

    /**
     * @var bool
     */
    private bool $defaultIsExpression = false;

    /**
     * @var bool
     */
    private bool $autoIncrement = false;

    /**
     * @var bool $primaryKey
     */
    private $primaryKey = false;

    /**
     * ColumnDefinition constructor.
     * @param string $name
     * @param bool $isAlter
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name, bool $isAlter = false)
    {
        if (empty($name) || !$this->isValidIdentifier($name)) {
            throw new \InvalidArgumentException(
                'Column name must be a valid identifier'
            );
        }
        $this->name = $name;
        $this->isAlter = $isAlter;
    }

    /**
     * Validates if a string is a valid SQL identifier
     * @param string $identifier
     * @return bool
     */
    private function isValidIdentifier(string $identifier): bool
    {
        // Basic validation: alphanumeric, underscores, starts with letter or underscore
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier) === 1;
    }

    /**
     * @param string $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function type(string $type): self
    {
        if (empty($type)) {
            throw new \InvalidArgumentException('Column type cannot be empty');
        }
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param int|null $length
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function rename(
        string $name,
        string $type,
        ?int $length = null
    ): self {
        if (empty($name) || !$this->isValidIdentifier($name)) {
            throw new \InvalidArgumentException(
                'New column name must be a valid identifier'
            );
        }
        if (empty($type)) {
            throw new \InvalidArgumentException('Column type cannot be empty');
        }

        $this->newName = $name;
        $this->type = $type;

        if ($length) {
            $this->length = $length;
        }

        return $this;
    }

    /**
     * @param int|string $length
     * @return $this
     */
    public function length(int|string $length): self
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @param bool $autoIncrement
     * @return $this
     */
    public function primaryKey(bool $autoIncrement = true): self
    {
        $this->autoIncrement = $autoIncrement;
        $this->primaryKey = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function autoIncrement(): self
    {
        $this->autoIncrement = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function null(): self
    {
        $this->null = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function notNull(): self
    {
        $this->null = false;
        return $this;
    }

    /**
     * @param mixed $defaultVal
     * @return $this
     */
    public function default($defaultVal): self
    {
        if ($defaultVal instanceof Raw) {
            $this->defaultVal = $defaultVal->getValue();
            $this->defaultIsExpression = true;
        } else {
            $this->defaultVal = $defaultVal;
            $this->defaultIsExpression = false;
        }
        return $this;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function generate(string $query): self
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
        $statement[] =
            '`' .
            $this->name .
            '`' .
            ($this->isAlter
                ? ' `' . ($this->newName ?: $this->name) . '`'
                : '');

        if ($this->type) {
            $type = strtoupper($this->type);

            if ($this->length !== null) {
                $type .= '(' . (string) $this->length . ')';
            }

            $statement[] = $type;
        }

        if ($this->generateQuery) {
            $generateStatement =
                'GENERATED ALWAYS as (' . $this->generateQuery . ')';

            $statement[] = $generateStatement;
            return implode(' ', $statement);
        }

        $statement[] = $this->null ? 'NULL' : 'NOT NULL';

        if ($this->defaultVal !== false) {
            if ($this->defaultIsExpression) {
                // For expressions (like JSON_ARRAY(), JSON_OBJECT()), use as-is wrapped in parentheses
                $statement[] = 'DEFAULT (' . (string) $this->defaultVal . ')';
            } elseif (is_string($this->defaultVal)) {
                // Use proper SQL escaping by replacing single quotes with doubled quotes
                $escapedValue = str_replace("'", "''", $this->defaultVal);
                $statement[] = "DEFAULT '" . $escapedValue . "'";
            } elseif (is_null($this->defaultVal)) {
                $statement[] = 'DEFAULT NULL';
            } else {
                $statement[] = 'DEFAULT ' . (string) $this->defaultVal;
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
