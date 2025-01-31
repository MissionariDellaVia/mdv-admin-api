<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Comment::with('gospel');

        // Optional filtering by gospel_id
        if ($request->has('gospel_id')) {
            $query->where('gospel_id', $request->gospel_id);
        }

        $comments = $query->orderBy('comment_order')->get();
        return $this->sendResponse($comments);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gospel_id' => 'required|exists:gospels,gospel_id',
            'comment_text' => 'required|string',
            'extra_info' => 'nullable|string',
            'youtube_link' => 'nullable|url|max:255',
            'is_latest' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $comment = Comment::create($validator->validated());
        return $this->sendResponse($comment, 201);
    }

    public function show(Comment $comment): JsonResponse
    {
        $comment->load('gospel');
        return $this->sendResponse($comment);
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gospel_id' => 'sometimes|required|exists:gospels,gospel_id',
            'comment_text' => 'sometimes|required|string',
            'extra_info' => 'nullable|string',
            'youtube_link' => 'nullable|url|max:255',
            'comment_order' => 'sometimes|required|integer|min:1',
            'is_latest' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $comment->update($validator->validated());
        return $this->sendResponse($comment);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();
        return $this->sendResponse(null, 204);
    }
}
