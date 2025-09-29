# DRY Dbi
Improved DRY databases

#### Index

* [Installation](#installation)
* [Usage](#usage)
* [Testing](#testing)
* [Documentation](docs/index.md)
* [Release Process](docs/release-process.md)

#### Installation
```bash
composer require tallieutallieu/dry-dbi
```

#### Usage

##### Define service provider
```php
<?php

namespace app\provider;

use Repository\PageRepository;
use Oak\Contracts\Container\ContainerInterface;
use Oak\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function register(ContainerInterface $app)
	{
		$app->set(Repository\PageRepository::class, Repository\PageRepository::class);
	}
	
	public function boot(ContainerInterface $app)
	{
	    //
	}
}

```

##### Repository definition
Extend from BaseRepository for some default behaviour.
```php
<?php

namespace Repository;

use Tnt\Dbi\BaseRepository;
use Tnt\Dbi\Criteria\OrderBy;
use Tnt\Dbi\Criteria\IsTrue;
use Tnt\Dbi\QueryBuilder;
use Model\Page;

class PageRepository extends BaseRepository
{
	protected $model = Page::class;
	
	/**
	* Initial method for default actions
	*/
	public function init()
	{
		$this->addCriteria(new OrderBy('sort_index'));
	}
	
	public function visible()
	{
		$this->addCriteria(new IsTrue('is_visible'));
		
		return $this;
	}
	
	/**
	* Use querybuilder directly for custom actions
	*/
	public function types(array $types)
	{
		$this->useQueryBuilder(function(QueryBuilder $queryBuilder) use ($types) {

			$queryBuilder->whereGroup(function(QueryBuilder $queryBuilder) use ($types) {
				foreach ($types as $type) {
					$queryBuilder->where('type', '=', $type->id, 'OR');
				}
			});
		});

		return $this;
	}
	
	/**
	* Use querybuilder for table joins (left, right, inner)
	*/
	public function expertise(Expertise $expertise)
   	{
		$this->useQueryBuilder(function(QueryBuilder $queryBuilder) {
		    $queryBuilder->leftJoin('project_expertise')->on('project', '=', 'project.id');
		});

		return $this;
	    }
}
```

##### Use repository
```php
<?php

namespace Controller;

use app\container\Application;

class pages
{
    public static function get(Request $request)
    {
        $app = Application::get();
        $pageRepository = $app->get(PageRepository::class);
        
        $visiblePages = $pageRepository->visible()->get();
    }
}

```

#### Testing

Run the test suite using [Pest PHP](https://pestphp.com/):

```bash
# Run all tests
make test

# Run tests with verbose output  
make test-verbose

# Run tests with coverage
make test-coverage
```

This will execute all tests in the `tests/` directory through Docker. For more information about testing, see [tests/README.md](tests/README.md).

#### Release Management

This project uses automated release management with GitHub Actions. Releases are automatically created when:

- Changes to `composer.json` version field are pushed to the `main` branch
- Manual workflow dispatch is triggered from the GitHub Actions tab

For detailed information about the release process, versioning strategy, and CI/CD integration, see [Release Process Documentation](docs/release-process.md).

**Quick version bump:**
```bash
# Bump patch version (3.1.0 -> 3.1.1)
php scripts/bump-version.php patch

# Bump minor version (3.1.0 -> 3.2.0)  
php scripts/bump-version.php minor

# Bump major version (3.1.0 -> 4.0.0)
php scripts/bump-version.php major
```

##### Criteria
Name					| Description
--------------------------------------- | ---------------------------------------------------------
Equals($column, $value)			| Check if column equals specific value
GreaterThan($column, $value)		| Check if column is greater than specific value
GreaterThanOrEqual($column, $value)	| Check if column is greater than or equals specific value
GroupBy($column)			| Create a group by on a column
In($column, $values)			| Check if column value is in array of values
IsFalse($column)			| Check on falsy value
IsNull($column)				| Check on NULL
IsTrue($column)				| Check on truthy value
LessThan($column, $value)		| Check if column is less than specific value
LessThanOrEqual($column, $value)	| Check if column is less than or equals specific value
LimitOffset($limit, $offset)		| Create a limit/offset on query
NotEquals($column, $value)		| Check if column and value are not equals
NotNull($column)			| Check on NOT NULL
OrEquals($column, $value)		| Check if column equals value with OR connector
OrderBy($column, $order = 'ASC')	| Order by column with default ordering ASC
Raw($value, $bindings)			| Build a custom query
