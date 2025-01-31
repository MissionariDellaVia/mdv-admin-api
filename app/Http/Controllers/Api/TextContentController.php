<?php

namespace App\Http\Controllers\Api;

use App\Models\TextContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TextContentController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = TextContent::query();

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        $contents = $query->orderBy('title')->get();
        return $this->sendResponse($contents);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:text_contents',
            'content' => 'required|string',
            'is_published' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $textContent = TextContent::create($data);
        return $this->sendResponse($textContent, 201);
    }

    public function show(TextContent $textContent): JsonResponse
    {
        return $this->sendResponse($textContent);
    }

    public function update(Request $request, TextContent $textContent): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:text_contents,slug,' . $textContent->content_id . ',content_id',
            'content' => 'sometimes|required|string',
            'is_published' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();

        // Update slug only if title is changed and slug is not provided
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $textContent->update($data);
        return $this->sendResponse($textContent);
    }

    public function destroy(TextContent $textContent): JsonResponse
    {
        $textContent->delete();
        return $this->sendResponse(null, 204);
    }

    public function findBySlug(string $slug): JsonResponse
    {
        $content = TextContent::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return $this->sendResponse($content);
    }
}
