<?php

namespace App\Services\Health;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckService {
    public function check(): array {
        try {
            $this->checkDatabase();
            $this->checkCache();

            return [
                'status' => 'ok',
                'services' => [
                    'database' => 'up',
                    'cache' => 'up',
                ],
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (Throwable $e) {
              return [
                  'status' => 'error',
                  'message' => 'Service unavailable',
              ];
        }
    }
  
    private function checkDatabase(): void {
        DB::select('SELECT 1');
    }
    
    private function checkCache(): void {
        Cache::put('healthcheck', 'ok', 5);
    }
}
