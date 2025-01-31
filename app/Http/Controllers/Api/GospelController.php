<?php

namespace App\Http\Controllers\Api;

use App\Models\Gospel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class GospelController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $gospels = Gospel::with(['comments', 'gospelWays'])->get();
        return $this->sendResponse($gospels);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gospel_verse' => 'required|string|max:255|unique:gospels',
            'gospel_text' => 'required|string',
            'evangelist' => 'required|string|max:100',
            'sacred_text_reference' => 'nullable|string',
            'liturgical_period' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $gospel = Gospel::create($validator->validated());
        return $this->sendResponse($gospel, 201);
    }

    public function show($id): JsonResponse
    {
        try {
            $gospel = Gospel::findOrFail($id);
            $gospel->load(['comments', 'gospelWays']);
            return $this->sendResponse($gospel);
        } catch (ModelNotFoundException $e) {
            Log::error('Gospel not found', ['id' => $id, 'exception' => $e]);
            return $this->sendError('Gospel not found', null, 404);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred', ['exception' => $e]);
            return $this->sendError('An unexpected error occurred', null, 500);
        }
    }

    public function update(Request $request, Gospel $gospel): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gospel_verse' => 'sometimes|required|string|max:255|unique:gospels,gospel_verse,' . $gospel->gospel_id . ',gospel_id',
            'gospel_text' => 'sometimes|required|string',
            'evangelist' => 'sometimes|required|string|max:100',
            'sacred_text_reference' => 'nullable|string',
            'liturgical_period' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $gospel->update($validator->validated());
        return $this->sendResponse($gospel);
    }

    public function destroy(Gospel $gospel): JsonResponse
    {
        $gospel->delete();
        return $this->sendResponse(null, 204);
    }
}
