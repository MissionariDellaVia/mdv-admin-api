<?php

namespace App\Http\Controllers\Api;

use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::query();

        if ($request->has('mime_type')) {
            $query->where('mime_type', 'like', $request->mime_type . '%');
        }

        $media = $query->orderBy('created_at', 'desc')->get();
        return $this->sendResponse($media);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $file = $request->file('file');
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('media', $fileName, 'public');

        $media = Media::create([
            'title' => $request->title,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'alt_text' => $request->alt_text,
            'description' => $request->description
        ]);

        return $this->sendResponse($media, 201);
    }

    public function show(Media $media): JsonResponse
    {
        return $this->sendResponse($media);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'file' => 'nullable|file|max:10240',
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('file')) {
            // Delete old file
            Storage::disk('public')->delete($media->file_path);

            // Store new file
            $file = $request->file('file');
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('media', $fileName, 'public');

            $data['file_name'] = $fileName;
            $data['file_path'] = $filePath;
            $data['mime_type'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();
        }

        $media->update($data);
        return $this->sendResponse($media);
    }

    public function destroy(Media $media): JsonResponse
    {
        // Delete file from storage
        Storage::disk('public')->delete($media->file_path);

        $media->delete();
        return $this->sendResponse(null, 204);
    }
}
