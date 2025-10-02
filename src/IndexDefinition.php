<?php

namespace Tnt\Dbi;

class IndexDefinition
{
    /**
     * @var array<int, string>
     */
    private array $columns;

    /**
     * @var string|null
     */
    private ?string $identifierName = null;

    /**
     * IndexDefinition constructor.
     * @param string|array<int, string> $columns
     */
    public function __construct(string|array $columns)
    {
        $this->columns = is_array($columns) ? $columns : [$columns];
    }

    /**
     * @param string $identifierName
     * @return $this
     */
    public function identifier(string $identifierName): self
    {
        $this->identifierName = $identifierName;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        if ($this->identifierName) {
            return $this->identifierName;
        }

        return 'idx_' . implode('_', $this->columns);
    }

    /**
     * @return array<int, string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return bool
     */
    public function isComposite(): bool
    {
        return count($this->columns) > 1;
    }
}
