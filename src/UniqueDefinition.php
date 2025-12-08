<?php

namespace Tnt\Dbi;

class UniqueDefinition
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
     * UniqueDefinition constructor.
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

        return 'uq_' . implode('_', $this->columns);
    }

    /**
     * @return array<int, string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Get the column name (for backwards compatibility with single-column unique constraints)
     * @return string
     * @deprecated Use getColumns() instead
     */
    public function getColumn(): string
    {
        return $this->columns[0];
    }

    /**
     * @return bool
     */
    public function isComposite(): bool
    {
        return count($this->columns) > 1;
    }
}
