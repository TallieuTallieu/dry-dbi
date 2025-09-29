# Raw Statements

The Raw class allows you to include custom SQL expressions in your queries while maintaining parameter binding safety.

## Raw Class

Implements `StatementInterface` to provide custom SQL with optional parameter bindings.

### Constructor

```php
public function __construct(string $value, array $bindings = [])
```

- `$value`: The SQL string
- `$bindings`: Array of parameter values for prepared statement binding

### Methods

#### getValue()

```php
public function getValue(): string
```

Returns the SQL string.

#### getBindings()

```php
public function getBindings(): array
```

Returns the parameter bindings array.

## Usage Examples

### Simple Raw SQL

```php
// Custom SELECT expression
$qb->select(new Raw('COUNT(*) as total'));

// Custom WHERE condition
$qb->where(new Raw('YEAR(created_at)'), '=', 2023);
```

### Raw SQL with Parameters

```php
// Using parameters for safety
$qb->select(new Raw('CONCAT(?, " - ", ?)', ['prefix', 'suffix']));

// Complex condition with parameters
$qb->where(new Raw('JSON_EXTRACT(metadata, ?) = ?', ['$.status', 'active']));
```

### In Criteria

Raw statements can be used within criteria:

```php
class JsonFieldCriteria implements CriteriaInterface
{
    private $field;
    private $path;
    private $value;
    
    public function __construct($field, $path, $value)
    {
        $this->field = $field;
        $this->path = $path;
        $this->value = $value;
    }
    
    public function apply(QueryBuilder $queryBuilder)
    {
        $queryBuilder->where(
            new Raw('JSON_EXTRACT(?, ?)', [$this->field, $this->path]),
            '=',
            $this->value
        );
    }
}
```

### Advanced Examples

#### Date Functions

```php
// Group by month
$qb->groupBy(new Raw('MONTH(created_at)'));

// Order by custom expression
$qb->orderBy(new Raw('FIELD(status, ?, ?, ?)', ['pending', 'active', 'inactive']));
```

#### Subqueries

```php
// Subquery in SELECT
$qb->select(new Raw(
    '(SELECT COUNT(*) FROM posts WHERE user_id = users.id) as post_count'
));

// Subquery in WHERE
$qb->where(new Raw(
    'id IN (SELECT user_id FROM posts WHERE created_at > ?)',
    ['2023-01-01']
));
```

#### Mathematical Operations

```php
// Calculated fields
$qb->selectAs(new Raw('price * quantity'), 'total_amount');

// Complex calculations with parameters
$qb->select(new Raw(
    'ROUND(rating * ?, 2) as weighted_rating',
    [1.5]
));
```

#### String Functions

```php
// String manipulation
$qb->where(new Raw('LOWER(name)'), 'LIKE', '%search%');

// Full-text search
$qb->where(new Raw(
    'MATCH(title, content) AGAINST(? IN BOOLEAN MODE)',
    ['+mysql +database']
));
```

## Best Practices

### Parameter Binding

Always use parameter binding for user input:

```php
// Good - uses parameter binding
$searchTerm = $_GET['search'];
$qb->where(new Raw('MATCH(content) AGAINST(?)', [$searchTerm]));

// Bad - vulnerable to SQL injection
$qb->where(new Raw("MATCH(content) AGAINST('$searchTerm')"));
```

### Complex Expressions

Break down complex expressions for readability:

```php
// Complex scoring algorithm
$scoringExpression = new Raw(
    '(views * ?) + (likes * ?) + (comments * ?) as engagement_score',
    [0.1, 0.5, 1.0]
);
$qb->selectAs($scoringExpression, 'score');
```

### Reusable Raw Statements

Create reusable Raw statements for common expressions:

```php
class SqlExpressions
{
    public static function fullName(): Raw
    {
        return new Raw('CONCAT(first_name, " ", last_name)');
    }
    
    public static function ageFromBirthdate(): Raw
    {
        return new Raw('FLOOR(DATEDIFF(NOW(), birthdate) / 365.25)');
    }
}

// Usage
$qb->selectAs(SqlExpressions::fullName(), 'full_name')
   ->selectAs(SqlExpressions::ageFromBirthdate(), 'age');
```

## Integration with Other Components

### In QueryBuilder Methods

Raw statements work seamlessly with all QueryBuilder methods:

```php
$qb->select(new Raw('DISTINCT category'))
   ->where(new Raw('YEAR(created_at)'), '=', 2023)
   ->orderBy(new Raw('RAND()'))
   ->having(new Raw('COUNT(*)'), '>', 5);
```

### In Criteria

```php
class RandomOrderCriteria implements CriteriaInterface
{
    public function apply(QueryBuilder $queryBuilder)
    {
        $queryBuilder->orderBy(new Raw('RAND()'));
    }
}
```

### With Joins

```php
$qb->leftJoin('categories')
   ->on(new Raw('products.category_id'), '=', new Raw('categories.id'))
   ->on(new Raw('categories.is_active'), '=', new Raw('1'));
```