<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\RepositoryInterface;
use Tnt\Dbi\Criteria\LimitOffset;
use Tnt\Dbi\Criteria\OrderBy;

/**
 * Provides common pagination and sorting criteria for repositories.
 */
class BaseRepository extends Repository implements RepositoryInterface
{
    /**
     * Limit the number of results and optionally skip an initial set of rows.
     *
     * @param int $amount Maximum number of results to return.
     * @param int $offset Number of results to skip before returning rows.
     * @return $this
     */
    public function amount(int $amount = 30, int $offset = 0): self
    {
        $this->addCriteria(new LimitOffset($amount, $offset));

        return $this;
    }

    /**
     * Sort results by a column in the requested direction.
     *
     * @param string $column Column to sort by.
     * @param string $order Sort direction, such as ASC or DESC.
     * @return $this
     */
    public function orderBy(string $column, string $order = 'ASC'): self
    {
        $this->addCriteria(new OrderBy($column, $order));

        return $this;
    }
}
