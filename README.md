# DRY Dbi
Improved DRY databases

#### Installation
```ssh
composer require dietervyncke/dry-dbi
```

#### Basic example usage

##### Define repository service provider
```php
<?php

namespace app\provider;

use Repository\PageRepository;
use Oak\Contracts\Container\ContainerInterface;
use Oak\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
	public function boot(ContainerInterface $app)
	{
	    //
	}

	public function register(ContainerInterface $app)
	{
		$app->set(Repository\PageRepository::class, Repository\PageRepository::class);
	}
}

```

##### Repository definition
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