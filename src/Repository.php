<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\CriteriaCollectionInterface;
use Tnt\Dbi\Contracts\CriteriaInterface;

abstract class Repository
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @var CriteriaCollectionInterface
     */
    private $criteria;

    /**
     * Holds callables that directly use the Query Builder
     * @var array
     */
    private $queryBuilderUses = [];

    /**
     * Repository constructor.
     * @param CriteriaCollectionInterface $criteria
     */
    public function __construct(CriteriaCollectionInterface $criteria)
    {
        $this->criteria = $criteria;
        $this->init();
    }

    /**
     * Called upon repository creation
     */
    protected function init() {}

    /**
     * @param CriteriaInterface $criteria
     */
    protected function addCriteria(CriteriaInterface $criteria)
    {
        $this->criteria->addCriteria($criteria);
    }

    /**
     * Applies all criteria to the query
     * @param QueryBuilder $queryBuilder
     */
    private function applyCriteria(QueryBuilder $queryBuilder)
    {
        $criteria = $this->criteria->getCriteria();

        foreach ($criteria as $criterion) {
            $criterion->apply($queryBuilder);
        }
    }

    /**
     * Registers a direct use of the Query Builder
     * @param callable $call
     */
    protected function useQueryBuilder(callable $call)
    {
        $this->queryBuilderUses[] = $call;
    }

    /**
     * Calls all direct uses of the Query Builder
     * @param QueryBuilder $queryBuilder
     */
    private function applyQueryBuilderUses(QueryBuilder $queryBuilder)
    {
        foreach ($this->queryBuilderUses as $use) {
            $use($queryBuilder);
        }
    }

    /**
     * Creates an instance of the query builder
     * @return QueryBuilder
     */
    private function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = new QueryBuilder();
        $queryBuilder->table(($this->model)::TABLE);
        $this->applyCriteria($queryBuilder);
        $this->applyQueryBuilderUses($queryBuilder);
        return $queryBuilder;
    }

    /**
     * Get all results
     * @return mixed
     */
    final public function get()
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->selectAll();

        return $this->fetchAll($queryBuilder);
    }

    /**
     * Get the first result
     * @return mixed
     */
    final public function first()
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->selectAll();
        $queryBuilder->limit(1);

        return $this->fetchOne($queryBuilder);
    }

    /**
     * Gets the query and all of its parameters as an array starting with the query, followed by the params
     * @param QueryBuilder $queryBuilder
     * @return array
     */
    private function getQuery(QueryBuilder $queryBuilder): array
    {
        $queryBuilder->build();

        $query = $queryBuilder->getQuery();
        $params = $queryBuilder->getParameters();

        array_unshift($params, $query);

        return $params;
    }

    /**
     * Fetches one model or throw a FetchException
     * @param QueryBuilder $queryBuilder
     * @return mixed
     */
    private function fetchOne(QueryBuilder $queryBuilder)
    {
        return call_user_func_array([$this->model, 'query_row'], $this->getQuery($queryBuilder));
    }

    /**
     * Fetches multiple models
     * @param QueryBuilder $queryBuilder
     * @return mixed
     */
    private function fetchAll(QueryBuilder $queryBuilder)
    {
        return call_user_func_array([$this->model, 'query'], $this->getQuery($queryBuilder));
    }
}