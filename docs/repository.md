# Repository

The Repository pattern provides a clean abstraction layer for database operations, allowing you to work with collections of domain objects without dealing with database specifics.

## Repository (Abstract)

Base abstract class that all repositories extend from.

### Constructor

```php
public function __construct(CriteriaCollectionInterface $criteria)
```

### Static Factory

```php
static function create(): static
```

Creates a new repository instance with a fresh CriteriaCollection.

### Protected Methods

#### init()

```php
protected function init(): void
```

Called upon repository creation. Override this method to set default criteria or configuration.

#### addCriteria()

```php
protected function addCriteria(CriteriaInterface $criteria): void
```

Adds a criteria to the repository's criteria collection.

#### useQueryBuilder()

```php
protected function useQueryBuilder(callable $call): void
```

Registers a direct use of the QueryBuilder for complex queries that can't be expressed through criteria.

### Public Methods

#### get()

```php
final public function get(): mixed
```

Executes the query and returns all matching results.

#### first()

```php
final public function first(): mixed
```

Executes the query with LIMIT 1 and returns the first matching result.

### Properties

- `$model` (string): The model class this repository works with. Must have a `TABLE` constant.

## BaseRepository

Extends Repository with common functionality.

### Methods

#### amount()

```php
public function amount(int $amount = 30, int $offset = 0): self
```

Adds LIMIT and OFFSET to the query.

#### orderBy()

```php
public function orderBy(string $column, string $order = 'ASC'): self
```

Adds ORDER BY clause to the query.

## Usage Example

```php
class UserRepository extends BaseRepository
{
    protected $model = User::class;
    
    protected function init()
    {
        // Default ordering
        $this->addCriteria(new OrderBy('created_at', 'DESC'));
    }
    
    public function active(): self
    {
        $this->addCriteria(new IsTrue('is_active'));
        return $this;
    }
    
    public function byRole(string $role): self
    {
        $this->addCriteria(new Equals('role', $role));
        return $this;
    }
    
    public function withPosts(): self
    {
        $this->useQueryBuilder(function(QueryBuilder $qb) {
            $qb->leftJoin('posts')->on('users.id', '=', 'posts.user_id');
        });
        return $this;
    }
}

// Usage
$userRepo = UserRepository::create();
$activeAdmins = $userRepo->active()->byRole('admin')->get();
$firstUser = $userRepo->first();
```