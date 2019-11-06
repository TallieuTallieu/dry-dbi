<?php

namespace Tnt\Dbi;

class JoinBuilder extends BuildHandler
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $on = [];

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $joinTypes = [
            'left' => 'LEFT',
            'right' => 'RIGHT',
            'inner' => 'INNER',
        ];

        if (! isset($joinTypes[$type])) {
            throw new \InvalidArgumentException('Unknown join type');
        }

        $this->type = $joinTypes[$type];
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return BuildHandler
     */
    public function on(string $field, string $operator, string $value): BuildHandler
    {
        $this->on[] = [$field, $operator, $value,];
        return $this;
    }

    /**
     * @return void
     */
    public function build()
    {
        $this->addToQuery($this->type.' JOIN '.$this->quote($this->getTable()));

        if (count($this->on)) {

            $onStatements = [];

            foreach ($this->on as $on) {
                $onStatements[] = $this->withTablePrefix($on[0]).' '.$on[1].' '.$this->withTablePrefix($on[2]);
            }

            $this->addToQuery(' ON ' . implode(' AND ', $onStatements));
        }
    }
}