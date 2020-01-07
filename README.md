# DRY Dbi
Improved DRY databases

#### Index

* [Installation](#installation)
* [Usage](#usage)

#### Installation
```ssh
composer require dietervyncke/dry-dbi
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
use Model\Page;

class PageRepository extends BaseRepository
{
	protected $model = Page::class;
	
	public function init()
	{
		$this->addCriteria( new OrderBy( 'sort_index' ) );
	}
	
	public function visible()
	{
		$this->addCriteria( new IsTrue( 'is_visible' ) );
		
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

##### Criteria
Name					| Description
--------------------------------------- | ---------------------------------------------------------
Equals($column, $value)			| Check if column is equals to specific value
GreateThan($column, $value)		| Check if column is greater than specific value
GreaterThanOrEqual($column, $value)	| Check if column is greater than or equals specific value
GroupBy($column)			| Create a group by on a column
IsFalse($column)			| Check on falsy value
IsNull($column)				| Check on NULL
IsTrue($column)				| Check on truthy value
LessThan($column, $value)		| Check if column is less than specific value
LessThanOrEquals($column, $value)	| Check if column is less than or equals specific value
LimitOffset($limit, $offet)		| Create a limit/offset on query
NotEquals($column, $value)		| Check if column and value are not equals
OrderBy($column, $order = 'ASC')	| Order by column with default ordering ASC
