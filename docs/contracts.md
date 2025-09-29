# Contracts

The contracts define the interfaces that various components of the library implement.

## CriteriaInterface

Interface for all criteria classes that can be applied to queries.

```php
interface CriteriaInterface
{
    public function apply(QueryBuilder $queryBuilder);
}
```

### apply()

Applies the criteria to a QueryBuilder instance. This method should modify the QueryBuilder by adding WHERE conditions, ORDER BY clauses, or other query modifications.

**Implementation Example:**

```php
class CustomCriteria implements CriteriaInterface
{
    private $column;
    private $value;
    
    public function __construct($column, $value)
    {
        $this->column = $column;
        $this->value = $value;
    }
    
    public function apply(QueryBuilder $queryBuilder)
    {
        $queryBuilder->where($this->column, '=', $this->value);
    }
}
```

## CriteriaCollectionInterface

Interface for managing collections of criteria.

```php
interface CriteriaCollectionInterface
{
    public function addCriteria(CriteriaInterface $criteria);
    public function getCriteria(): array;
}
```

### addCriteria()

Adds a criteria instance to the collection.

### getCriteria()

Returns an array of all criteria in the collection.

**Implementation Example:**

```php
class CustomCriteriaCollection implements CriteriaCollectionInterface
{
    private $criteria = [];
    
    public function addCriteria(CriteriaInterface $criteria)
    {
        $this->criteria[] = $criteria;
    }
    
    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
```

## StatementInterface

Interface for SQL statement components that can provide both a value and parameter bindings.

```php
interface StatementInterface
{
    public function getValue(): string;
    public function getBindings(): array;
}
```

### getValue()

Returns the SQL string representation of the statement.

### getBindings()

Returns an array of parameter values for prepared statement binding.

**Implementation Example:**

```php
class CustomStatement implements StatementInterface
{
    private $sql;
    private $bindings;
    
    public function __construct(string $sql, array $bindings = [])
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }
    
    public function getValue(): string
    {
        return $this->sql;
    }
    
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
```

## RepositoryInterface

Empty marker interface for repository classes.

```php
interface RepositoryInterface
{
    // Empty interface - serves as a marker
}
```

This interface is currently empty and serves as a marker interface for type hinting and future extensibility.

## Usage in the Library

These contracts enable:

1. **Polymorphism**: Different implementations can be swapped without changing client code
2. **Testability**: Easy to create mock implementations for testing
3. **Extensibility**: New criteria and statement types can be added without modifying existing code
4. **Type Safety**: Proper type hints ensure correct usage

**Example Usage:**

```php
// Using contracts for type hints
function applyCriteria(CriteriaInterface $criteria, QueryBuilder $qb)
{
    $criteria->apply($qb);
}

// Using in repository
class UserRepository extends Repository
{
    public function withCustomCriteria(CriteriaInterface $criteria): self
    {
        $this->addCriteria($criteria);
        return $this;
    }
}
```