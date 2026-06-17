<?php
namespace App\Models;

use App\Config\Database;

class Simulation {
    public int $id;
    public ?int $user_id;
    public string $type;
    public string $params; 
    public string $results; 
    public ?string $share_token;
    public string $created_at;

    public static function create(array $data): self {
        $pdo = Database::getConnection();
        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare(
            'INSERT INTO simulations (user_id, type, params, results, share_token) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['type'],
            json_encode($data['params']),
            json_encode($data['results']),
            $token
        ]);
        $sim = new self();
        $sim->id = (int)$pdo->lastInsertId();
        $sim->user_id = $data['user_id'] ?? null;
        $sim->type = $data['type'];
        $sim->params = json_encode($data['params']);
        $sim->results = json_encode($data['results']);
        $sim->share_token = $token;
        return $sim;
    }

    public static function findByUser(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM simulations WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findByShareToken(string $token): ?self {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM simulations WHERE share_token = ?');
        $stmt->execute([$token]);
        $data = $stmt->fetch();
        return $data ? self::hydrate($data) : null;
    }

    public static function findByIdAndUser(int $id, int $userId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM simulations WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    private static function hydrate(array $data): self {
        $sim = new self();
        $sim->id = (int)$data['id'];
        $sim->user_id = $data['user_id'] ? (int)$data['user_id'] : null;
        $sim->type = $data['type'];
        $sim->params = $data['params'];
        $sim->results = $data['results'];
        $sim->share_token = $data['share_token'];
        $sim->created_at = $data['created_at'];
        return $sim;
    }
}