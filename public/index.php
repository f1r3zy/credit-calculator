<?php
declare(strict_types=1);
session_start();

if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Request;

$router = new Router();

$router->add('GET', '/', 'App\Controllers\PageController@home');
$router->add('GET', '/home', 'App\Controllers\PageController@home');
$router->add('GET', '/calculator', 'App\Controllers\PageController@calculator');
$router->add('GET', '/login', 'App\Controllers\PageController@login');
$router->add('GET', '/register', 'App\Controllers\PageController@register');
$router->add('GET', '/profile', 'App\Controllers\PageController@profile');
$router->add('GET', '/simulations', 'App\Controllers\PageController@simulations');
$router->add('GET', '/share/{token}', 'App\Controllers\PageController@share');
$router->add('POST', '/api/register', 'App\Controllers\AuthController@register');
$router->add('POST', '/api/login', 'App\Controllers\AuthController@login');
$router->add('POST', '/api/logout', 'App\Controllers\AuthController@logout');
$router->add('GET', '/api/me', 'App\Controllers\AuthController@me');
$router->add('POST', '/api/calculate', 'App\Controllers\CalculatorController@calculate');
$router->add('POST', '/api/export-csv', 'App\Controllers\CalculatorController@exportCSV');
$router->add('POST', '/api/early-repayment', 'App\Controllers\CalculatorController@earlyRepayment');
$router->add('POST', '/api/simulations', 'App\Controllers\SimulationController@save');
$router->add('GET', '/api/simulations', 'App\Controllers\SimulationController@list');
$router->add('GET', '/api/simulations/{id}', 'App\Controllers\SimulationController@show');
$router->add('GET', '/despre-noi', 'App\Controllers\PageController@despreNoi');
$router->add('GET', '/contact', 'App\Controllers\PageController@contact');
$router->add('GET', '/termeni-si-conditii', 'App\Controllers\PageController@termeni');
$router->add('GET', '/confidentialitate', 'App\Controllers\PageController@confidentialitate');
$router->add('GET', '/api/reference-rates/latest', 'App\Controllers\CalculatorController@latestRate');

$request = new Request();
$response = $router->dispatch($request);
$response->send();