<?php

use Core\Application;
use App\Middleware\AuthMiddleware;

$router = Application::app()->router;

// Public routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/auth/google/callback', 'AuthController@googleCallback');

// Protected routes
$router->get('/logout', 'AuthController@logout')->middleware([AuthMiddleware::class]);