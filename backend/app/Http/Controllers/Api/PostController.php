<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\SearchPostRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly PostRepository $posts
    ) {}

    public function index(): JsonResponse {
        $posts = Post::query()
            ->where('status', 'published')
            ->with(['user:id,name', 'tags:id,name,slug'])
            ->orderByDesc('id')
            ->cursorPaginate(
                perPage: 10,
                cursor: $request->input('cursor')
            );

        return response()->json($posts);
    }

    public function search(SearchPostRequest $request): JsonResponse {
        $posts = $this->posts->search(
            query: $request->q,
            limit: (int) $request->limit,
            page: (int) $request->page,
        );

        return response()->json($posts);
    }

    public function show(Post $post): JsonResponse {
        abort_unless($post->status === 'published', 404);
        return response()->json(
            $post->load('tags', 'user')
        );
    }

    public function store(StorePostRequest $request): JsonResponse {
        $post = $this->posts->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json($post, 201);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse {
        $this->authorize('update', $post);
        $post = $this->posts->update(
            $post,
            $request->validated()
        );

        return response()->json($post);
    }

    public function destroy(Request $request, Post $post): JsonResponse {
        $this->authorize('delete', $post);
        $this->posts->delete($post);

        return response()->json(['status' => 'ok']);
    }
}
