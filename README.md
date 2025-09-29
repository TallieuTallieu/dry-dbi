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

This project uses **branch-based automated release management** with GitHub Actions. Releases are automatically created when you push to branches with version information:

- `release/v3.2.0` - Creates release v3.2.0
- `hotfix/v3.1.1` - Creates hotfix release v3.1.1  
- `version/3.2.0` - Creates release v3.2.0
- `v3.2.0` - Creates release v3.2.0
- `feature/v3.2.0-api` - Creates release v3.2.0

For detailed information about supported branch patterns, versioning strategy, and CI/CD integration, see [Release Process Documentation](docs/release-process.md).

**Quick release creation:**
```bash
# Create patch release (hotfix)
php scripts/bump-version.php patch
git checkout -b hotfix/v3.1.1 && git add . && git commit -m "chore: bump to 3.1.1" && git push origin hotfix/v3.1.1

# Create minor release  
php scripts/bump-version.php minor
git checkout -b release/v3.2.0 && git add . && git commit -m "chore: bump to 3.2.0" && git push origin release/v3.2.0

# Create major release
php scripts/bump-version.php major
git checkout -b release/v4.0.0 && git add . && git commit -m "chore: bump to 4.0.0" && git push origin release/v4.0.0
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
