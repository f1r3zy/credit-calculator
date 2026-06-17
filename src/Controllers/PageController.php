<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Simulation;

class PageController
{
    public function home(): Response
    {
        ob_start();
        include __DIR__ . '/../../views/home.php';
        return new Response(200, ob_get_clean());
    }

    public function calculator(): Response
    {
        ob_start();
        include __DIR__ . '/../../views/calculator.php';
        return new Response(200, ob_get_clean());
    }

    public function login(): Response
    {
        ob_start();
        include __DIR__ . '/../../views/login.php';
        return new Response(200, ob_get_clean());
    }

    public function register(): Response
    {
        ob_start();
        include __DIR__ . '/../../views/register.php';
        return new Response(200, ob_get_clean());
    }

    public function profile(): Response
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        ob_start();
        include __DIR__ . '/../../views/profile.php';
        return new Response(200, ob_get_clean());
    }

    public function simulations(): Response
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        ob_start();
        include __DIR__ . '/../../views/simulations.php';
        return new Response(200, ob_get_clean());
    }

    public function share(Request $request, string $token): Response
    {
        $sim = Simulation::findByShareToken($token);
        if (!$sim) {
            return new Response(404, 'Simularea nu a fost găsită.');
        }
        ob_start();
        include __DIR__ . '/../../views/calculator.php'; 
        return new Response(200, ob_get_clean());
    }
}