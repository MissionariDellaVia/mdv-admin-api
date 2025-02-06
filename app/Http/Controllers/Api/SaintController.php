<?php

namespace App\Http\Controllers\Api;

use App\Models\Gospel;
use App\Models\Saint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaintController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        // Get pagination parameters with default values
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $saints = Saint::query()->paginate($limit, ['*'], 'page', $page);
        return $this->sendResponse($saints);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'biography' => 'nullable|string',
            'recurrence_date' => 'nullable|date',
            'feast_day' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $saint = Saint::create($validator->validated());
        return $this->sendResponse($saint, 201);
    }

    public function show(Saint $saint): JsonResponse
    {
        return $this->sendResponse($saint);
    }

    public function update(Request $request, Saint $saint): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'biography' => 'nullable|string',
            'recurrence_date' => 'nullable|date',
            'feast_day' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $saint->update($validator->validated());
        return $this->sendResponse($saint);
    }

    public function destroy(Saint $saint): JsonResponse
    {
        $saint->delete();
        return $this->sendResponse(null, 204);
    }

    public function searchName(Request $request): JsonResponse
    {
        $query = $request->input('query', '');

        if (empty($query)) {
            $results = Saint::query()
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get(['saint_id', 'name']);
        }

        $results = Saint::where('name', 'like', '%' . $query . '%')
            ->limit(10)
            ->get(['saint_id', 'name']);

        return $this->sendResponse($results);
    }
}
