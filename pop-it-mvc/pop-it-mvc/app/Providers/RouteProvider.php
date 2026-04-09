<?php

namespace Providers;

use Src\Provider\AbstractProvider;
use Src\Route;

class RouteProvider extends AbstractProvider
{

    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->app->bind('route', Route::single()->setPrefix($this->app->settings->getRootPath()));

        //Загружаем маршруты из стандартного файла
        require_once __DIR__ . '/../..' . $this->app->settings->getRoutePath() . '/web.php';

    }
}
