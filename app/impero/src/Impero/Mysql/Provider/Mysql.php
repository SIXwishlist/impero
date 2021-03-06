<?php namespace Impero\Mysql\Provider;

use Impero\Mysql\Controller\Database;
use Impero\Mysql\Controller\DatabaseApi;
use Impero\Mysql\Controller\User;
use Impero\Mysql\Record\Database\Resolver as DatabaseResolver;
use Impero\Mysql\Record\User\Resolver as UserResolver;
use Pckg\Framework\Provider;
use Pckg\Framework\Router\Route\Group;
use Pckg\Framework\Router\Route\Route;

class Mysql extends Provider
{

    public function routes()
    {
        return [
            'url' => maestro_urls(Database::class, 'database', 'database', DatabaseResolver::class, 'mysql/databases')
                     + maestro_urls(User::class, 'user', 'user', UserResolver::class, 'mysql/users'),

            (new Group([
                           'controller' => DatabaseApi::class,
                           'urlPrefix'  => '/api/database',
                           'namePrefix' => 'api.impero.database',
                       ]))->routes([
                                       '' => (new Route('', 'database'))->resolvers(),
                                   ]),
        ];
    }

}