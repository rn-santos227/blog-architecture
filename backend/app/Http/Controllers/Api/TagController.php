<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\IndexTagRequest;
use App\Services\Tag\TagService;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    public function __construct(
        private readonly TagService $tags
    ) {}

    public function index(IndexTagRequest $request): JsonResponse {
        $limit = (int) $request->input('limit', 50);
        $query = $request->input('q');

        $tags = $this->tags->listWithPostCounts($limit, $query);

        return response()->json($tags);
    }
}
