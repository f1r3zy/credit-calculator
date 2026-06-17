<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Csrf;
use App\Models\Simulation;

class SimulationController
{
    public function save(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::error(401, 'Neautentificat.');
        }

        if (!Csrf::validate($request->input('csrf_token', ''))) {
            return new Response(403, ['error' => 'Token CSRF invalid.']);
        }

        $data = $request->all();
        $sim = Simulation::create([
            'user_id' => Auth::user()->id,
            'type' => $data['type'] ?? 'nevoi_personale',
            'params' => $data['params'] ?? [],
            'results' => $data['results'] ?? []
        ]);
        return new Response(201, ['message' => 'Simulare salvată.', 'share_token' => $sim->share_token]);
    }

    public function list(): Response
    {
        if (!Auth::check()) {
            return Response::error(401, 'Neautentificat.');
        }
        $sims = Simulation::findByUser(Auth::user()->id);
        return new Response(200, ['simulations' => $sims]);
    }

    public function show(Request $request, string $id): Response
    {
        if (!Auth::check()) {
            return Response::error(401, 'Neautentificat.');
        }
        $sim = Simulation::findByIdAndUser((int)$id, Auth::user()->id);
        if (!$sim) {
            return Response::error(404, 'Simularea nu a fost găsită.');
        }
        return new Response(200, ['simulation' => $sim]);
    }
}