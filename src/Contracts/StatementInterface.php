<?php

namespace Tnt\Dbi\Contracts;

interface StatementInterface
{
    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @return array<int, mixed>
     */
    public function getBindings(): array;
}
