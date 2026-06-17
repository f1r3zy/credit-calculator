<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Csrf;
use App\Core\Auth;
use App\Models\User;

class AuthController
{
    public function register(Request $request): Response
    {
        if (!Csrf::validate($request->input('csrf_token', ''))) {
            return new Response(403, ['error' => 'Token CSRF invalid.']);
        }

        $data = $request->all();
        $v = new Validator();
        $v->required('name', $data['name'] ?? '')
          ->required('email', $data['email'] ?? '')->email('email', $data['email'] ?? '')
          ->required('password', $data['password'] ?? '');
        if ($v->hasErrors()) {
            return new Response(422, ['errors' => $v->getErrors()]);
        }
        if (User::findByEmail($data['email'])) {
            return new Response(409, ['error' => 'Email deja folosit.']);
        }
        $user = User::create($data);
        session_regenerate_id(true);
        Auth::login($user);

        return new Response(201, ['message' => 'Cont creat cu succes.']);
    }

    public function login(Request $request): Response
    {
        if (!Csrf::validate($request->input('csrf_token', ''))) {
            return new Response(403, ['error' => 'Token CSRF invalid.']);
        }

        $data = $request->all();
        $v = new Validator();
        $v->required('email', $data['email'] ?? '')->required('password', $data['password'] ?? '');
        if ($v->hasErrors()) {
            return new Response(422, ['errors' => $v->getErrors()]);
        }
        $user = User::findByEmail($data['email']);
        if (!$user || !$user->verifyPassword($data['password'])) {
            return new Response(401, ['error' => 'Credențiale incorecte.']);
        }

        session_regenerate_id(true);
        Auth::login($user);

        return new Response(200, ['message' => 'Autentificare reușită.']);
    }

    public function logout(): Response
    {
        Auth::logout();
        return new Response(200, ['message' => 'Deconectat.']);
    }

    public function me(): Response
    {
        if (!Auth::check()) {
            return Response::error(401, 'Neautentificat.');
        }
        $user = Auth::user();
        return new Response(200, ['user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]]);
    }
}