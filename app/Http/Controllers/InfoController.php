<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InfoController extends Controller
{
    public function getInfoUrl(Request $request) {
        Log::info('Request to getInfoUrl', ['query' => $request->query()]);
        $url = $request->query('url');

        if (!$url) {
            return response()->json(['success' => 0, 'message' => 'URL is required'], 400);
        }
    
        try {
            // Ambil metadata dari URL (misal: pakai OpenGraph atau Meta Tags)
            $response = Http::get($url);
            preg_match('/<title>(.*?)<\/title>/', $response->body(), $titleMatches);
            preg_match('/<meta name="description" content="(.*?)"/', $response->body(), $descMatches);
            preg_match('/<meta property="og:image" content="(.*?)"/', $response->body(), $imageMatches);
    
            return response()->json([
                'success' => 1,
                'link' => $url,
                'meta' => [
                    'title' => $titleMatches[1] ?? 'No title',
                    'description' => $descMatches[1] ?? 'No description available',
                    'image' => [
                        'url' => $imageMatches[1] ?? null
                    ]
                ]
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['success' => 0, 'message' => 'Failed to fetch metadata'], 500);
        }
    }
}
