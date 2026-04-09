<?php
namespace Src;

use Error;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;
use Src\Auth\Auth;

class Application
{
    private Settings $settings;
    private Route $route;
    private Capsule $dbManager;
    private Auth $auth;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        // ИЗМЕНЕННАЯ СТРОКА - вместо new Route() используем Route::single()
        $this->route = Route::single()->setPrefix($this->settings->getRootPath());

        $this->dbManager = new Capsule();

        $this->auth = new $this->settings->app['auth'];
        $this->dbRun();
        $this->auth::init(new $this->settings->app['identity']);
    }

    public function __get($key)
    {
        if ($key === 'settings') {
            return $this->settings;
        }
        throw new Error('Accessing a non-existent property');
    }

    private function dbRun()
    {
        $this->dbManager->addConnection($this->settings->getDbSetting());
        $this->dbManager->setEventDispatcher(new Dispatcher(new Container));
        $this->dbManager->setAsGlobal();
        $this->dbManager->bootEloquent();
    }

    public function run(): void
    {
        $this->route->start();
    }
}