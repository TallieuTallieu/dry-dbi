<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\StatementInterface;

class Raw implements StatementInterface
{
    /**
     * @var string
     */
    private string $value;

    /**
     * @var array<int, mixed>
     */
    private array $bindings;

    /**
     * Raw constructor.
     * @param string $value
     * @param array<int, mixed> $bindings
     */
    public function __construct(string $value, array $bindings = [])
    {
        $this->value = $value;
        $this->bindings = $bindings;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array<int, mixed>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
