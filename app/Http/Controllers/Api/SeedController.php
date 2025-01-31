<?php

namespace App\Http\Controllers\Api;

use App\Models\Seed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeedController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Seed::query();

        if ($request->has('color')) {
            $query->where('color', $request->color);
        }

        $seeds = $query->orderBy('created_at', 'desc')->get();
        return $this->sendResponse($seeds);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'verse_text' => 'required|string',
            'color' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $seed = Seed::create($validator->validated());
        return $this->sendResponse($seed, 201);
    }

    public function show(Seed $seed): JsonResponse
    {
        return $this->sendResponse($seed);
    }

    public function update(Request $request, Seed $seed): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'verse_text' => 'sometimes|required|string',
            'color' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $seed->update($validator->validated());
        return $this->sendResponse($seed);
    }

    public function destroy(Seed $seed): JsonResponse
    {
        $seed->delete();
        return $this->sendResponse(null, 204);
    }

    public function random(): JsonResponse
    {
        $seed = Seed::inRandomOrder()->first();
        return $this->sendResponse($seed);
    }
}
