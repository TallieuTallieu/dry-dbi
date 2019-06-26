<?php

namespace Tnt\Dbi\Contracts;

interface CriteriaCollectionInterface
{
	/**
	 * @param CriteriaInterface $criteria
	 */
	public function addCriteria(CriteriaInterface $criteria);

	/**
	 * @return array
	 */
	public function getCriteria(): array;
}