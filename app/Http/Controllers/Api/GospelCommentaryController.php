<?php

namespace App\Http\Controllers\Api;

use App\Models\GospelCommentary;
use App\Models\Saint;
use App\Utils\DateFormatter;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GospelCommentaryController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // Get pagination parameters with default values
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = GospelCommentary::with(['gospel', 'saint']);
        $commentaries = $query->orderBy('calendar_date')->paginate($limit, ['*'], 'page', $page);

        return $this->sendResponse($commentaries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'calendar_date' => 'required|date',
            'gospel_id' => 'required|exists:gospels,gospel_id',
            'saint_id' => 'nullable|exists:saints,saint_id',
            'comment_text' => 'required|string',
            'extra_info' => 'nullable|string',
            'youtube_link' => 'nullable|string|max:255',
            'liturgical_season' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
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

        $commentary = GospelCommentary::create($data);
        $commentary->load(['gospel', 'saint']);
        return $this->sendResponse($commentary);
    }

    /**
     * Display the specified resource.
     */
    public function show(GospelCommentary $gospelCommentary): JsonResponse
    {
        return response()->json($gospelCommentary);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GospelCommentary $gospelCommentary): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'calendar_date' => 'required|date',
            'gospel_id' => 'required|exists:gospels,gospel_id',
            'saint_id' => 'nullable|exists:saints,saint_id',
            'comment_text' => 'required|string',
            'extra_info' => 'nullable|string',
            'youtube_link' => 'nullable|string|max:255',
            'liturgical_season' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $gospelCommentary->update($validator->validated());
        return $this->sendResponse($gospelCommentary);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GospelCommentary $gospelCommentary): JsonResponse
    {
        $gospelCommentary->delete();
        return $this->sendResponse(null, 204);
    }

    public function getCompleteGospelCommentary(Request $request): JsonResponse
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
            $gospelCommentary = GospelCommentary::whereDate('calendar_date', $searchDate)
                ->with(['gospel', 'saint'])
                ->orderBy('created_at', 'desc')
                ->first();

            Log::info('Found GospelWay: ', ['data' => $gospelCommentary]);

            if (!$gospelCommentary) {
                return $this->sendError('Gospel Way not found', [
                    'date' => ["No calendar entry found for date {$searchDate}"]
                ], 404);
            }

            $previous_comments = GospelCommentary::where('gospel_id', $gospelCommentary->gospel_id)
                ->where('commentary_id', '!=', $gospelCommentary->commentary_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Prepare response data
            $responseData = [
                'saint' => $gospelCommentary->saint ? [
                    'saint_id' => $gospelCommentary->saint->saint_id,
                    'name' => $gospelCommentary->saint->name,
                    'biography' => $gospelCommentary->saint->biography,
                    'feast_day' => DateFormatter::formatDate($gospelCommentary->saint->feast_day),
                ] : null,
                'calendar_info' => [
                    'calendar_date' => $gospelCommentary->calendar_date,
                    'liturgical_season' => $gospelCommentary->liturgical_season,
                ],
                'gospel' => $gospelCommentary->gospel ? [
                    'gospel_id' => $gospelCommentary->gospel->gospel_id,
                    'gospel_verse' => $gospelCommentary->gospel->gospel_verse,
                    'gospel_text' => $gospelCommentary->gospel->gospel_text,
                    'evangelist' => $gospelCommentary->gospel->evangelist,
                    'sacred_text_reference' => $gospelCommentary->gospel->sacred_text_reference,
                    'is_active' => $gospelCommentary->gospel->is_active
                ] : null,
                'comment' => [
                    'comment_text' => $gospelCommentary->comment_text,
                    'extra_info' => $gospelCommentary->extra_info,
                    'youtube_link' => $gospelCommentary->youtube_link,
                ],
                'previous_comments' => $previous_comments->map(function ($previous_comment) {
                    return [
                        'calendar_date' => $previous_comment->calendar_date,
                        'comment_text' => $previous_comment->comment_text,
                        'extra_info' => $previous_comment->extra_info,
                        'youtube_link' => $previous_comment->youtube_link
                    ];
                })
            ];

            return $this->sendResponse($responseData);

        } catch (Exception $e) {
            return $this->sendError('Error retrieving complete Gospel Way data',
                ['error' => $e->getMessage()], 500);
        }
    }
}
