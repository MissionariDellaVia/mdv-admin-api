<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use function Pest\Laravel\get;

class ContactController extends Controller
{
    public function index(): JsonResponse
    {
        $contacts = Contact::with('place')->get();
        return response()->json($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_type' => 'required|in:Email,Phone,Fax,Other',
            'contact_group' => 'nullable|string|max:50',
            'contact_type_description' => 'nullable|string|max:50',
            'contact_value' => 'required|string|max:150',
            'place_id' => 'required|exists:places,place_id',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation Error', 'messages' => $validator->errors()], 422);
        }

        $contact = Contact::create($validator->validated());
        return response()->json($contact, 201);
    }

    public function show($id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        return response()->json($contact);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'contact_type' => 'required|in:Email,Phone,Fax,Other',
            'contact_group' => 'nullable|string|max:50',
            'contact_type_description' => 'nullable|string|max:50',
            'contact_value' => 'required|string|max:150',
            'place_id' => 'nullable|exists:places,place_id',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation Error', 'messages' => $validator->errors()], 422);
        }

        $contact->update($validator->validated());
        return response()->json($contact);
    }

    public function destroy($id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return response()->json(null, 204);
    }
}
