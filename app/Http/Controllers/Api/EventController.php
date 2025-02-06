<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::with('place');

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Filter by holy mass
        if ($request->has('is_holy_mass')) {
            $query->where('is_holy_mass', $request->boolean('is_holy_mass'));
        }

        $events = $query->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        return $this->sendResponse($events);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s|after:start_time',
            'place' => 'nullable|string',
            'is_holy_mass' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|string|max:50',
            'place_id' => 'required|exists:places,place_id'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $event = Event::create($validator->validated());
        return $this->sendResponse($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        $event->load('place');
        return $this->sendResponse($event);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s|after:start_time',
            'place' => 'sometimes|string|max:255',
            'is_holy_mass' => 'boolean',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|string|max:50',
            'place_id' => 'nullable|exists:places,place_id'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $event->update($validator->validated());
        $event->load('place');
        return $this->sendResponse($event);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();
        return $this->sendResponse(null, 204);
    }
}
