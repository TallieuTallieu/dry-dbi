<?php

use Tnt\Dbi\CriteriaCollection;
use Tnt\Dbi\Criteria\Equals;
use Tnt\Dbi\Criteria\GreaterThan;
use Tnt\Dbi\Criteria\OrderBy;
use Tnt\Dbi\Criteria\LimitOffset;

describe('CriteriaCollection', function () {
    it('creates empty collection', function () {
        $collection = new CriteriaCollection();

        expect($collection->getCriteria())
            ->toBeArray()
            ->and($collection->getCriteria())
            ->toBeEmpty();
    });

    it('adds single criterion', function () {
        $collection = new CriteriaCollection();
        $criteria = new Equals('status', 'active');

        $collection->addCriteria($criteria);

        expect($collection->getCriteria())
            ->toHaveCount(1)
            ->and($collection->getCriteria()[0])
            ->toBe($criteria);
    });

    it('adds multiple criteria', function () {
        $collection = new CriteriaCollection();
        $criteria1 = new Equals('status', 'active');
        $criteria2 = new GreaterThan('age', 18);
        $criteria3 = new OrderBy('name', 'ASC');

        $collection->addCriteria($criteria1);
        $collection->addCriteria($criteria2);
        $collection->addCriteria($criteria3);

        expect($collection->getCriteria())
            ->toHaveCount(3)
            ->and($collection->getCriteria()[0])
            ->toBe($criteria1)
            ->and($collection->getCriteria()[1])
            ->toBe($criteria2)
            ->and($collection->getCriteria()[2])
            ->toBe($criteria3);
    });

    it('maintains order of added criteria', function () {
        $collection = new CriteriaCollection();
        $orderBy = new OrderBy('created_at', 'DESC');
        $limit = new LimitOffset(10);
        $equals = new Equals('type', 'post');

        $collection->addCriteria($equals);
        $collection->addCriteria($orderBy);
        $collection->addCriteria($limit);

        $criteria = $collection->getCriteria();

        expect($criteria[0])
            ->toBe($equals)
            ->and($criteria[1])
            ->toBe($orderBy)
            ->and($criteria[2])
            ->toBe($limit);
    });

    it('returns array of criteria', function () {
        $collection = new CriteriaCollection();
        $collection->addCriteria(new Equals('id', 1));

        expect($collection->getCriteria())->toBeArray();
    });

    it('allows adding same criterion type multiple times', function () {
        $collection = new CriteriaCollection();
        $criteria1 = new Equals('status', 'active');
        $criteria2 = new Equals('type', 'post');

        $collection->addCriteria($criteria1);
        $collection->addCriteria($criteria2);

        expect($collection->getCriteria())->toHaveCount(2);
    });
});
