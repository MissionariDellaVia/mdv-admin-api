<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::with(['contactType', 'place']);

        // Optional filtering by contact type
        if ($request->has('contact_type_id')) {
            $query->where('contact_type_id', $request->contact_type_id);
        }

        $contacts = $query->where('is_active', true)->get();
        return $this->sendResponse($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_type_id' => 'required|exists:contact_types,contact_type_id',
            'contact_value' => 'required|string|max:255',
            'place_id' => 'nullable|exists:places,place_id',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $contact = Contact::create($validator->validated());
        $contact->load(['contactType', 'place']);
        return $this->sendResponse($contact, 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $contact->load(['contactType', 'place']);
        return $this->sendResponse($contact);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_type_id' => 'sometimes|required|exists:contact_types,contact_type_id',
            'contact_value' => 'sometimes|required|string|max:255',
            'place_id' => 'nullable|exists:places,place_id',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $contact->update($validator->validated());
        $contact->load(['contactType', 'place']);
        return $this->sendResponse($contact);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();
        return $this->sendResponse(null, 204);
    }
}
