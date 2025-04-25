<?php

namespace Tnt\Dbi;

class JoinBuilder extends BuildHandler
{
    private $alias;

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
     * Set an alias for the joined table
     *
     * @param string $alias The alias to use for the joined table
     * @return $this
     */
    public function as(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function build()
    {
        $join = $this->type.' JOIN '.$this->quote($this->getTable());

        if (!empty($this->alias)) {
            $join .= ' AS ' . $this->quote($this->alias);
        }

        $this->addToQuery($join);

        if (count($this->on)) {
            $onStatements = [];

            foreach ($this->on as $on) {
                $prefix = $on[3] ?? true;

                if ($prefix || strpos($on[0], '.') !== false) {
                    $on[0] = $this->withTablePrefix($on[0]);
                }

                if ($prefix || strpos($on[2], '.') !== false) {
                    $on[2] = $this->withTablePrefix($on[2]);
                }

                $onStatements[] = $on[0] . ' ' . $on[1] . ' ' . $on[2];
            }

            $this->addToQuery(' ON ' . implode(' AND ', $onStatements));
        }
    }
}