<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HealthCheckApi extends TestCase
{
    use DatabaseTransactions;

    public function test_health_check_returns_ok(): void {
        $response = $this->getJson('/api/v1/health');
        $response
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('services.database', 'up')
            ->assertJsonPath('services.cache', 'up');
    }
}
