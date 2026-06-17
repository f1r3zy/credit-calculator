<?php
namespace App\Models;
use App\Config\Database;
class ReferenceRate {
    public static function latest(): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM reference_rates ORDER BY rate_date DESC LIMIT 1');
        return $stmt->fetch() ?: null;
    }
    public static function insert(array $data): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO reference_rates (rate_date, base_rate, overnight_credit, overnight_deposit) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['rate_date'],
            $data['base_rate'],
            $data['overnight_credit'] ?? null,
            $data['overnight_deposit'] ?? null
        ]);
    }
}   