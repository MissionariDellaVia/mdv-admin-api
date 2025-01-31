<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Address::query();

        // Filter by city if provided
        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $addresses = $query->with(['contacts', 'events'])
            ->orderBy('city')
            ->orderBy('street')
            ->get();

        return $this->sendResponse($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $address = Address::create($validator->validated());
        return $this->sendResponse($address, 201);
    }

    public function show(Address $address): JsonResponse
    {
        $address->load(['contacts', 'events']);
        return $this->sendResponse($address);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $address->update($validator->validated());
        return $this->sendResponse($address);
    }

    public function destroy(Address $address): JsonResponse
    {
        // Check for related records before deleting
        if ($address->contacts()->exists() || $address->events()->exists()) {
            return $this->sendError('Cannot delete address with related records', [], 409);
        }

        $address->delete();
        return $this->sendResponse(null, 204);
    }
}
