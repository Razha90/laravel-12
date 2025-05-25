<?php

namespace App\Http\Controllers;

use App\Models\ImagesContent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteImage extends Controller
{
    public function deleteImage(string $url)
    {
        // Ambil path relatif dari URL
        $relativePath = str_replace(url('/storage'), '', $url); // hasil: /images/xxx.png

        // Hapus file
        $deleted = Storage::disk('public')->delete($relativePath);

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'File deleted']);
        } else {
            return response()->json(['success' => false, 'message' => 'File not found or failed to delete']);
        }
    }

    public function deleteByContentId($contentId)
    {
        try {
            $images = ImagesContent::where('content_id', $contentId)->get();
            if ($images->isEmpty()) {
                return false;
            }
            foreach ($images as $image) {
                $response = $this->deleteImageFromUrl($image->path);

                if (!$response->getData()->success) {
                    Log::error('DeleteImage deleteByContentId, ' . $response->getData()->message);
                } else {
                    $image->delete();
                }
            }

            return response()->json(['success' => true, 'message' => 'All images deleted']);
        } catch (\Throwable $th) {
            Log::error('DeleteImage deleteByContentId, ' . $th->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete images']);
        }
    }

    public function deleteImageFromUrl(string $url)
    {
        try {
            // $relativePath = str_replace(url('/storage'), '', $url);
            // $deleted = Storage::disk('public')->delete($relativePath);
            $relativePath = str_replace(url('/storage'), '', $url);
            $relativePath = str_replace('/storage', '', $relativePath);
            $relativePath = ltrim($relativePath, '/'); // hilangkan slash awal agar jadi "images/xxx.jpg"
        
            Log::info('DeleteImage relativePath: ' . $relativePath);
        
            $deleted = Storage::disk('public')->delete($relativePath);
            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'File deleted']);
            } else {
                return response()->json(['success' => false, 'message' => 'File not found or failed to delete']);
            }
        } catch (\Throwable $th) {
            Log::error('DeleteImage deleteImageFromUrl, ' . $th->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete image']);
        }
    }
}
