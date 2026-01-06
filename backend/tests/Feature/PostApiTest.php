<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_published_posts(): void {
        $user = User::factory()->create();
        $published = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $draft = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->getJson('/api/v1/posts');

        $response->assertOk()->assertJsonCount(1, 'data');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($published->id));
        $this->assertFalse($ids->contains($draft->id));
    }

    public function test_show_returns_published_post_and_hides_drafts(): void {
        $user = User::factory()->create();
        $published = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $draft = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->getJson('/api/v1/posts/' . $published->id)
            ->assertOk()
            ->assertJsonPath('id', $published->id);

        $this->getJson('/api/v1/posts/' . $draft->id)
            ->assertNotFound();
    }

    public function test_search_returns_results_from_repository(): void {
        $postA = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $postB = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);

        $results = collect([$postA, $postB]);

        $this->mock(PostRepository::class, function ($mock) use ($results) {
            $mock->shouldReceive('search')
                ->once()
                ->with('laravel', 10, 1)
                ->andReturn($results);
        });

        $this->getJson('/api/v1/posts/search?q=laravel')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_store_creates_post_via_repository(): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'New Post',
            'body' => 'Post body content.',
            'status' => 'published',
        ];

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->mock(PostRepository::class, function ($mock) use ($payload, $user, $post) {
            $mock->shouldReceive('create')
                ->once()
                ->with(Mockery::on(function ($data) use ($payload) {
                    return $data['title'] === $payload['title']
                        && $data['body'] === $payload['body']
                        && $data['status'] === $payload['status'];
                }), $user->id)
                ->andReturn($post);
        });

        $this->postJson('/api/v1/posts', $payload)
            ->assertCreated()
            ->assertJsonPath('id', $post->id);
    }

    public function test_update_updates_post_via_repository(): void {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        Gate::define('update', fn (User $user, Post $post) => $user->id === $post->user_id);
        Sanctum::actingAs($user);

        $payload = ['title' => 'Updated Title'];
        $updated = $post->replicate();
        $updated->title = $payload['title'];

        $this->mock(PostRepository::class, function ($mock) use ($post, $payload, $updated) {
            $mock->shouldReceive('update')
                ->once()
                ->with($post, $payload)
                ->andReturn($updated);
        });

        $this->putJson('/api/v1/posts/' . $post->id, $payload)
            ->assertOk()
            ->assertJsonPath('title', $payload['title']);
    }

    public function test_destroy_deletes_post_via_repository(): void {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        Gate::define('delete', fn (User $user, Post $post) => $user->id === $post->user_id);
        Sanctum::actingAs($user);

        $this->mock(PostRepository::class, function ($mock) use ($post) {
            $mock->shouldReceive('delete')
                ->once()
                ->with($post);
        });

        $this->deleteJson('/api/v1/posts/' . $post->id)
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    }
}
