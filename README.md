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

This project uses **intelligent automated release management** with GitHub Actions. The system automatically infers the version bump type from your branch name and creates releases accordingly:

| Branch Type | Version Bump | Example |
|---|---|---|
| `feature/`, `feat/` | **MINOR** | 3.1.0 → 3.2.0 |
| `bug/`, `fix/`, `hotfix/` | **PATCH** | 3.1.0 → 3.1.1 |
| `breaking/`, `major/` | **MAJOR** | 3.1.0 → 4.0.0 |
| `chore/`, `docs/` | **PATCH** | 3.1.0 → 3.1.1 |

**🎫 Shortcut Integration**: Branches like `feature/sc-8322--description` automatically trigger **MINOR** releases.

For detailed information about all supported patterns and manual overrides, see [Release Process Documentation](docs/release-process.md).

**Zero-configuration releases:**
```bash
# Feature development (MINOR: 3.1.0 → 3.2.0)
git checkout -b feature/new-api && git push origin feature/new-api

# Bug fix (PATCH: 3.1.0 → 3.1.1)  
git checkout -b bug/fix-query-issue && git push origin bug/fix-query-issue

# Breaking change (MAJOR: 3.1.0 → 4.0.0)
git checkout -b breaking/api-redesign && git push origin breaking/api-redesign
```

**No manual version management needed** - just push your branch and get automatic releases! 🚀

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
