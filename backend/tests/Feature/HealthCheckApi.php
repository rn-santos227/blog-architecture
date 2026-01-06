<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckApi extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_returns_ok(): void {
        $response = $this->getJson('/api/v1/health');
        $response
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('services.database', 'up')
            ->assertJsonPath('services.cache', 'up');
    }
}
