<?php

namespace Tnt\Dbi;

use dry\contracts\container\ContainerInterface;
use dry\ServiceProvider;
use Tnt\Dbi\Contracts\CriteriaCollectionInterface;
use Tnt\Dbi\Contracts\RepositoryInterface;

class RepositoryProvider extends ServiceProvider
{
    public function boot(ContainerInterface $app)
    {
        //
    }

    public function register(ContainerInterface $app)
    {
        $app->set(
            CriteriaCollectionInterface::class,
            CriteriaCollection::class
        );
        $app->set(RepositoryInterface::class, BaseRepository::class);
        $app->set(QueryBuilder::class, QueryBuilder::class);
    }
}
