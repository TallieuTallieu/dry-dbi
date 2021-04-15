<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\RepositoryInterface;
use Tnt\Dbi\Criteria\LimitOffset;
use Tnt\Dbi\Criteria\OrderBy;

class BaseRepository extends Repository implements RepositoryInterface
{
    public function amount($amount = 30, $offset = 0)
    {
        $this->addCriteria(new LimitOffset($amount, $offset));

        return $this;
    }

    public function orderBy($column, $order = 'ASC')
    {
        $this->addCriteria(new OrderBy($column, $order));

        return $this;
    }
}