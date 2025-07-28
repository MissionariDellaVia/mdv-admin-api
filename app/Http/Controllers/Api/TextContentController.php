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
            'highlights' => 'nullable|array',
            'highlights.*.start' => 'required_with:highlights|integer|min:0',
            'highlights.*.end' => 'required_with:highlights|integer|gt:highlights.*.start',
            'highlights.*.color' => 'required_with:highlights|string|in:yellow,green,blue,pink,purple',
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
            'highlights' => 'nullable|array',
            'highlights.*.start' => 'required_with:highlights|integer|min:0',
            'highlights.*.end' => 'required_with:highlights|integer|gt:highlights.*.start',
            'highlights.*.color' => 'required_with:highlights|string|in:yellow,green,blue,pink,purple',
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

    /**
     * Add a highlight to text content
     */
    public function addHighlight(Request $request, TextContent $textContent): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start' => 'required|integer|min:0',
            'end' => 'required|integer|gt:start',
            'color' => 'required|string|in:yellow,green,blue,pink,purple'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();
        
        $success = $textContent->addHighlight($data['start'], $data['end'], $data['color']);
        
        if (!$success) {
            return $this->sendError('Failed to add highlight', [], 400);
        }

        return $this->sendResponse($textContent->fresh());
    }

    /**
     * Remove a highlight from text content
     */
    public function removeHighlight(Request $request, TextContent $textContent): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'index' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $index = $request->input('index');
        $success = $textContent->removeHighlight($index);
        
        if (!$success) {
            return $this->sendError('Failed to remove highlight', [], 400);
        }

        return $this->sendResponse($textContent->fresh());
    }

    /**
     * Clear all highlights from text content
     */
    public function clearHighlights(TextContent $textContent): JsonResponse
    {
        $textContent->clearHighlights();
        return $this->sendResponse($textContent->fresh());
    }

    /**
     * Get highlighted content as HTML
     */
    public function getHighlightedContent(TextContent $textContent): JsonResponse
    {
        $highlightedContent = $textContent->getHighlightedContent();
        
        return $this->sendResponse([
            'content' => $highlightedContent,
            'title' => $textContent->title,
            'highlights_count' => count($textContent->highlights ?? [])
        ]);
    }

    /**
     * Export highlighted content as text
     */
    public function exportHighlightedText(TextContent $textContent): JsonResponse
    {
        $exportText = $textContent->exportHighlightedText();
        
        return response()->json([
            'success' => true,
            'data' => [
                'content' => $exportText,
                'filename' => $textContent->slug . '-highlighted.txt'
            ]
        ]);
    }

    /**
     * Get the text highlighter view for a specific content
     */
    public function highlighterView(TextContent $textContent)
    {
        return view('text-highlighter', compact('textContent'));
    }
}
