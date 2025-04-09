<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AplicationController extends Controller
{
    public function sendAplication($message) {
        try {
            event(new \App\Events\AplicationNotification($message));
        } catch (\Throwable $th) {
            \Log::error('Aplication Event Error: ' . $th->getMessage());
        }   
    }
}
