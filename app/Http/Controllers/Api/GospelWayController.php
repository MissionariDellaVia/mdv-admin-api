<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Models\GospelWay;
use App\Models\Saint;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GospelWayController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        // Get pagination parameters with default values
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = GospelWay::with(['gospel', 'saint']);

        // Filter by date range
        if ($request->has('date')) {
            $query->whereDate('calendar_date', $request->date);
        }

        // Filter by liturgical season
        if ($request->has('liturgical_season')) {
            $query->where('liturgical_season', $request->liturgical_season);
        }

        $gospelWays = $query->orderBy('calendar_date')->paginate($limit, ['*'], 'page', $page);
        return $this->sendResponse($gospelWays);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'calendar_date' => 'required|date|unique:gospel_way',
            'gospel_id' => 'required|exists:gospels,gospel_id',
            'saint_id' => 'nullable|exists:saints,saint_id',
            'liturgical_season' => 'nullable|string|max:100',
            'is_solemnity' => 'boolean',
            'is_feast' => 'boolean',
            'is_memorial' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();

        if (empty($data['saint_id'])) {
            $calendarDate = Carbon::parse($data['calendar_date']);
            $saint = Saint::whereMonth('recurrence_date', $calendarDate->month)
                ->whereDay('recurrence_date', $calendarDate->day)
                ->first();
            if ($saint) {
                $data['saint_id'] = $saint->saint_id;
            }
        }

        $gospelWay = GospelWay::create($data);
        $gospelWay->load(['gospel', 'saint']);
        return $this->sendResponse($gospelWay, 201);
    }

    public function show(GospelWay $gospelWay): JsonResponse
    {
        $gospelWay->load(['gospel', 'saint']);
        return $this->sendResponse($gospelWay);
    }

    public function update(Request $request, GospelWay $gospelWay): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'calendar_date' => 'sometimes|required|date|unique:gospel_way,calendar_date,' . $gospelWay->gospel_way_id . ',gospel_way_id',
            'gospel_id' => 'sometimes|required|exists:gospels,gospel_id',
            'saint_id' => 'nullable|exists:saints,saint_id',
            'liturgical_season' => 'nullable|string|max:100',
            'is_solemnity' => 'boolean',
            'is_feast' => 'boolean',
            'is_memorial' => 'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $gospelWay->update($validator->validated());
        $gospelWay->load(['gospel', 'saint']);
        return $this->sendResponse($gospelWay);
    }

    public function destroy(GospelWay $gospelWay): JsonResponse
    {
        $gospelWay->delete();
        return $this->sendResponse(null, 204);
    }

    public function getCompleteGospelWay(Request $request): JsonResponse
    {

        // Validate request parameters
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        try {
            // Parse the date
            $searchDate = Carbon::parse($request->date)->format('Y-m-d');

            Log::info('Searching for date: ' . $searchDate);

            // Get the GospelWay entry for the specified date
            $gospelWay = GospelWay::whereDate('calendar_date', $searchDate)
                ->with(['gospel', 'saint'])
                ->first();

            Log::info('Query SQL: ' . GospelWay::whereDate('calendar_date', $searchDate)->toSql());
            Log::info('Found GospelWay: ', ['data' => $gospelWay]);

            if (!$gospelWay) {
                return $this->sendError('Gospel Way not found', [
                    'date' => ["No calendar entry found for date {$searchDate}"]
                ], 404);
            }

            // Get comments for the gospel
            $comments = Comment::where('gospel_id', $gospelWay->gospel_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Prepare response data
            $responseData = [
                'calendar_info' => [
                    'calendar_date' => $gospelWay->calendar_date,
                    'liturgical_season' => $gospelWay->liturgical_season,
                    'is_solemnity' => $gospelWay->is_solemnity,
                    'is_feast' => $gospelWay->is_feast,
                    'is_memorial' => $gospelWay->is_memorial,
                    'created_at' => $gospelWay->created_at,
                    'updated_at' => $gospelWay->updated_at
                ],
                'gospel' => $gospelWay->gospel ? [
                    'gospel_id' => $gospelWay->gospel->gospel_id,
                    'gospel_verse' => $gospelWay->gospel->gospel_verse,
                    'gospel_text' => $gospelWay->gospel->gospel_text,
                    'evangelist' => $gospelWay->gospel->evangelist,
                    'sacred_text_reference' => $gospelWay->gospel->sacred_text_reference,
                    'liturgical_period' => $gospelWay->gospel->liturgical_period,
                    'is_active' => $gospelWay->gospel->is_active
                ] : null,
                'saint' => $gospelWay->saint ? [
                    'saint_id' => $gospelWay->saint->saint_id,
                    'name' => $gospelWay->saint->name,
                    'biography' => $gospelWay->saint->biography,
                    'recurrence_date' => $gospelWay->saint->recurrence_date,
                    'feast_day' => $gospelWay->saint->feast_day,
                    'is_active' => $gospelWay->saint->is_active
                ] : null,
                'comments' => $comments->map(function ($comment) {
                    return [
                        'comment_id' => $comment->comment_id,
                        'comment_text' => $comment->comment_text,
                        'extra_info' => $comment->extra_info,
                        'youtube_link' => $comment->youtube_link,
                        'comment_order' => $comment->comment_order,
                        'is_latest' => $comment->is_latest,
                        'created_at' => $comment->created_at
                    ];
                })
            ];

            return $this->sendResponse($responseData);

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving complete Gospel Way data',
                ['error' => $e->getMessage()], 500);
        }
    }
}
