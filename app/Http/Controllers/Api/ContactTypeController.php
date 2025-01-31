<?php

namespace App\Http\Controllers\Api;

use App\Models\ContactType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactTypeController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $contactTypes = ContactType::with(['contacts' => function($query) {
            $query->where('is_active', true);
        }])->where('is_active', true)->get();

        return $this->sendResponse($contactTypes);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:50|unique:contact_types',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $contactType = ContactType::create($validator->validated());
        return $this->sendResponse($contactType, 201);
    }

    public function show(ContactType $contactType): JsonResponse
    {
        $contactType->load(['contacts' => function($query) {
            $query->where('is_active', true);
        }]);
        return $this->sendResponse($contactType);
    }

    public function update(Request $request, ContactType $contactType): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'sometimes|required|string|max:50|unique:contact_types,type_name,' . $contactType->contact_type_id . ',contact_type_id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $contactType->update($validator->validated());
        return $this->sendResponse($contactType);
    }

    public function destroy(ContactType $contactType): JsonResponse
    {
        // Check if there are any active contacts using this type
        if ($contactType->contacts()->where('is_active', true)->exists()) {
            return $this->sendError('Cannot delete contact type with active contacts', [], 409);
        }

        $contactType->delete();
        return $this->sendResponse(null, 204);
    }
}
