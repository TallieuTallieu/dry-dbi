<?php

namespace Tnt\Dbi\Contracts;

interface StatementInterface
{
	/**
	 * @return string
	 */
	public function getValue(): string;

	/**
	 * @return array
	 */
	public function getBindings(): array;
}