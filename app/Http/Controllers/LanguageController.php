<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function change(Request $request)
    {
        $lang = $request->lang;

        if (!in_array($lang, ['en', 'id', 'fr'])) {
            abort(400);
        }

        try {
            if (auth()->check()) {
                $user = auth()->user(); 
                $user->update(['language'=> $lang]);
            }
        } catch (\Throwable $th) {
            Log::error('Language Session'. $th);
        }

        Session::put('locale', $lang);

        return redirect()->back();
    }
}
