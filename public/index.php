<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Router;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Start session
session_start();

// Initialize Router
$router = new Router();

// Load routes (to be defined in a separate file or here)
$router->add('GET', '/', 'HomeController@index');
$router->add('GET', '/login', 'AuthController@showLogin');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/register', 'AuthController@showRegister');
$router->add('POST', '/register', 'AuthController@register');
$router->add('GET', '/logout', 'AuthController@logout');

// Client Routes
$router->add('GET', '/client/dashboard', 'ClientController@dashboard');
$router->add('GET', '/client/discovery', 'ClientController@discovery');
$router->add('GET', '/api/client/collectors/nearby', 'ClientController@getNearbyCollectors');

// Collector Routes
$router->add('GET', '/collector/dashboard', 'CollectorController@dashboard');
$router->add('POST', '/collector/status/update', 'CollectorController@updateStatus');
$router->add('POST', '/collector/profile/update', 'CollectorController@updateProfile');

// Admin Routes
$router->add('GET', '/admin/dashboard', 'AdminController@dashboard');

// Dispatch request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
