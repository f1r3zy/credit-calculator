<?php
namespace App\Core;
class Logger {
    public static function logCalculation(array $data): void {
        $logFile = __DIR__ . '/../../logs/calculations.log';
        $entry = date('Y-m-d H:i:s') . ' - ' . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}