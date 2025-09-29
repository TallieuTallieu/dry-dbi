# Criteria

Criteria provide a composable way to build query conditions. Each criteria class implements `CriteriaInterface` and can be applied to a QueryBuilder.

## Comparison Criteria

### Equals

```php
new Equals($column, $value)
```

Adds `WHERE column = value` condition.

```php
$repo->addCriteria(new Equals('status', 'active'));
```

### NotEquals

```php
new NotEquals($column, $value)
```

Adds `WHERE column != value` condition.

### GreaterThan

```php
new GreaterThan($column, $value)
```

Adds `WHERE column > value` condition.

```php
$repo->addCriteria(new GreaterThan('age', 18));
```

### GreaterThanOrEqual

```php
new GreaterThanOrEqual($column, $value)
```

Adds `WHERE column >= value` condition.

### LessThan

```php
new LessThan($column, $value)
```

Adds `WHERE column < value` condition.

### LessThanOrEqual

```php
new LessThanOrEqual($column, $value)
```

Adds `WHERE column <= value` condition.

## Null Checks

### IsNull

```php
new IsNull($column)
```

Adds `WHERE column IS NULL` condition.

### NotNull

```php
new NotNull($column)
```

Adds `WHERE column IS NOT NULL` condition.

## Boolean Checks

### IsTrue

```php
new IsTrue($column)
```

Adds `WHERE column = 1` condition.

```php
$repo->addCriteria(new IsTrue('is_published'));
```

### IsFalse

```php
new IsFalse($column)
```

Adds `WHERE column = 0` condition.

## List Operations

### In

```php
new In($column, array $values)
```

Adds `WHERE column IN (value1, value2, ...)` condition.

```php
$repo->addCriteria(new In('category_id', [1, 2, 3]));
```

Handles both string and numeric values with proper escaping.

## Logical Operations

### OrEquals

```php
new OrEquals($column, $value)
```

Adds `WHERE column = value` condition with OR connector instead of AND.

## Ordering and Limiting

### OrderBy

```php
new OrderBy($column, string $order = 'ASC')
```

Adds `ORDER BY column direction` clause.

```php
$repo->addCriteria(new OrderBy('created_at', 'DESC'));
```

### GroupBy

```php
new GroupBy($column)
```

Adds `GROUP BY column` clause.

### LimitOffset

```php
new LimitOffset(int $limit, int $offset = 0)
```

Adds `LIMIT` and optionally `OFFSET` clauses.

```php
$repo->addCriteria(new LimitOffset(10, 20)); // LIMIT 10 OFFSET 20
```

## Custom Criteria

Create custom criteria by implementing `CriteriaInterface`:

```php
class BetweenDates implements CriteriaInterface
{
    private $column;
    private $startDate;
    private $endDate;
    
    public function __construct($column, $startDate, $endDate)
    {
        $this->column = $column;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function apply(QueryBuilder $queryBuilder)
    {
        $queryBuilder->where($this->column, '>=', $this->startDate)
                     ->where($this->column, '<=', $this->endDate);
    }
}
```

## Usage Examples

```php
// Single criteria
$repo->addCriteria(new Equals('status', 'published'));

// Multiple criteria
$repo->addCriteria(new IsTrue('is_active'))
     ->addCriteria(new GreaterThan('created_at', '2023-01-01'))
     ->addCriteria(new In('category_id', [1, 2, 3]))
     ->addCriteria(new OrderBy('title'))
     ->addCriteria(new LimitOffset(10));

// In repository methods
public function published(): self
{
    $this->addCriteria(new Equals('status', 'published'));
    $this->addCriteria(new IsTrue('is_visible'));
    return $this;
}

public function recent(int $days = 30): self
{
    $date = date('Y-m-d', strtotime("-{$days} days"));
    $this->addCriteria(new GreaterThan('created_at', $date));
    return $this;
}
```