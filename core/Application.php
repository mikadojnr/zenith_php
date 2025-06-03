<?php

namespace Core;

use Core\Router;
use Core\Database;
use Core\Session;

class Application
{
    public Router $router;
    public Database $db;
    public Session $session;
    public static Application $app;

    public function __construct()
    {
        self::$app = $this;
        $this->router = new Router();
        $this->db = new Database();
        $this->session = new Session();
        
        $this->loadRoutes();
    }

    public function run()
    {
        $this->router->resolve();
    }

    private function loadRoutes()
    {
        // Load web routes
        require_once __DIR__ . '/../routes/web.php';
    }

    public static function app(): Application
    {
        return self::$app;
    }
}