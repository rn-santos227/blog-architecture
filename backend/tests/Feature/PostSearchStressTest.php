<?php

namespace Tests\Feature;

use App\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSearchStressTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_endpoint_handles_repeated_requests(): void {
        $iterations = 25;
        $results = collect([
            ['id' => 1, 'title' => 'Stress Result'],
        ]);

        $this->mock(PostRepository::class, function ($mock) use ($iterations, $results) {
            $mock->shouldReceive('search')
                ->times($iterations)
                ->with('laravel', 10, 1)
                ->andReturn($results);
        });

        for ($i = 0; $i < $iterations; $i++) {
            $this->getJson('/api/v1/posts/search?q=laravel')
                ->assertOk()
                ->assertJsonCount(1);
        }
    }
}
