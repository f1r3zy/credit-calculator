<?php
namespace App\Models;

use App\Config\Database;

class User {
    public int $id;
    public string $name;
    public string $email;
    public string $password_hash;

    public static function find(int $id): ?self {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? self::hydrate($data) : null;
    }

    public static function findByEmail(string $email): ?self {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        return $data ? self::hydrate($data) : null;
    }

    public static function create(array $data): self {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT)
        ]);
        $user = new self();
        $user->id = (int)$pdo->lastInsertId();
        $user->name = $data['name'];
        $user->email = $data['email'];
        return $user;
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password_hash);
    }

    private static function hydrate(array $data): self {
        $user = new self();
        $user->id = (int)$data['id'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        return $user;
    }
}