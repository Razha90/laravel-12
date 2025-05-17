<?php

namespace App\Http\Controllers;

use App\Models\ImagesContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SaveImageController extends Controller
{
    public function upload(Request $request)
    {
        try {
            if (!$request->hasFile('image')) {
                return response()->json(['success' => 0, 'message' => 'No file uploaded'], 400);
            }

            try {
                $request->merge(['content_id' => intval($request->content_id)]);
                $request->validate(
                    [
                        'content_id' => 'required|numeric|exists:contents,id', // Pastikan content_id ada di tabel contents
                    ],
                    [
                        'content_id.required' => __('auth.content_id.required'),
                        'content_id.integer' => __('auth.content_id.integer'),
                        'content_id.exists' => __('auth.content_id.exists'),
                    ],
                );
            } catch (ValidationException $e) {
                Log::error('saveImageController Upload Image' . $e);
                return response()->json(
                    [
                        'success' => 0,
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ],
                    422,
                );
            }


            $file = $request->file('image');
            $path = $file->store('images', 'public');

            try {
                ImagesContent::create([
                    'name' => $file->getClientOriginalName(),
                    'path' => asset("storage/$path"),
                    'content_id' => $request->content_id,
                    'classroom_id' => $request->classroom_id,
                ]);
            } catch (\Throwable $th) {
                Log::error('saveImageController Upload Image' . $th);
            }

            return response()->json([
                'success' => 1,
                'file' => [
                    'url' => asset("storage/$path"), // URL gambar yang diakses
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error('saveImageController Upload File' . $th);
            return response()->json(['success' => 0, 'message' => 'Internal Server Error'], 500);
        }
    }
    public function uploadFile(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['success' => 0, 'message' => 'No file uploaded'], 400);
            }

            try {
                $request->validate(
                    [
                        'content_id' => 'required|integer|exists:contents,id', // Pastikan content_id ada di tabel contents
                    ],
                    [
                        'content_id.required' => __('auth.content_id.required'),
                        'content_id.integer' => __('auth.content_id.integer'),
                        'content_id.exists' => __('auth.content_id.exists'),
                    ],
                );
            } catch (ValidationException $e) {
                Log::error('saveImageController Upload File' . $e);
                return response()->json(
                    [
                        'success' => 0,
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ],
                    422,
                );
            }

            $file = $request->file('file');
            $path = $file->store('files', 'public'); // Simpan ke storage/app/public/uploads

            try {
                ImagesContent::create([
                    'name' => $file->getClientOriginalName(),
                    'path' => asset("storage/$path"),
                    'content_id' => $request->content_id,
                    'classroom_id' => $request->classroom_id,
                ]);
            } catch (\Throwable $th) {
                Log::error('saveImageController Upload Image' . $th);
            }

            return response()->json([
                'success' => 1,
                'file' => [
                    'url' => asset("storage/$path"), // URL gambar yang diakses
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error('saveImageController Upload File' . $th);
            return response()->json(['success' => 0, 'message' => 'Internal Server Error'], 500);
        }
    }
}
