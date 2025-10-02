<?php

namespace Tnt\Dbi;

class UniqueDefinition
{
    /**
     * @var string $column
     */
    private $column;

    /**
     * UniqueDefinition constructor.
     * @param string $column
     */
    public function __construct(string $column)
    {
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'uq_' . $this->column;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }
}
