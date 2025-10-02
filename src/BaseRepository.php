<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\RepositoryInterface;
use Tnt\Dbi\Criteria\LimitOffset;
use Tnt\Dbi\Criteria\OrderBy;

class BaseRepository extends Repository implements RepositoryInterface
{
    public function amount(int $amount = 30, int $offset = 0): self
    {
        $this->addCriteria(new LimitOffset($amount, $offset));

        return $this;
    }

    public function orderBy(string $column, string $order = 'ASC'): self
    {
        $this->addCriteria(new OrderBy($column, $order));

        return $this;
    }
}
