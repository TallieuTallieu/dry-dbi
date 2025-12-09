<?php

namespace Tnt\Dbi;

class CheckDefinition
{
    /**
     * @var string
     */
    private string $column;

    /**
     * @var string
     */
    private string $expression;

    /**
     * @var string|null
     */
    private ?string $identifierName = null;

    /**
     * CheckDefinition constructor.
     *
     * The expression should be the full CHECK condition without the surrounding parentheses.
     *
     * Examples:
     * - Simple: new CheckDefinition('status', '`status` IN (0, 1, 2, 3)')
     * - Regex: new CheckDefinition('email', '`email` REGEXP \'^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$\'')
     * - Range: new CheckDefinition('age', '`age` >= 0 AND `age` <= 150')
     *
     * @param string $column The column name (used for generating the default constraint identifier)
     * @param string $expression The full check expression (e.g., "`status` IN (0, 1, 2, 3)")
     */
    public function __construct(string $column, string $expression)
    {
        $this->column = $column;
        $this->expression = $expression;
    }

    /**
     * Set a custom identifier name for the constraint
     *
     * @param string $identifierName
     * @return $this
     */
    public function identifier(string $identifierName): self
    {
        $this->identifierName = $identifierName;
        return $this;
    }

    /**
     * Get the constraint identifier
     *
     * Returns the custom identifier if set, otherwise generates one as 'chk_{column}'
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        if ($this->identifierName) {
            return $this->identifierName;
        }

        return 'chk_' . $this->column;
    }

    /**
     * Get the column name
     *
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Get the check expression
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }
}
