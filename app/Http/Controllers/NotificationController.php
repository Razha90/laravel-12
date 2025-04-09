<?php

namespace App\Http\Controllers;

use App\Events\NotificationsEvent;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function sendNotification($datas)
    {
        try {
            $data = [
                'title' => $datas['title'],
                'body' => $datas['body'],
                'user_id' => $datas['user_id'],
            ];

            $validated = Validator::make(
                $data,
                [
                    'title' => 'required|min:5|max:150|string',
                    'body' => 'required|min:15|max:2000|string',
                    'user_id' => 'required',
                ],
                [
                    'title.required' => 'Title is required',
                    'title.min' => 'Title must be at least 5 characters',
                    'title.max' => 'Title must be at most 150 characters',
                    'title.string' => 'Title must be a string',
                    'body.required' => 'Body is required',
                    'body.min' => 'Body must be at least 15 characters',
                    'body.max' => 'Body must be at most 2000 characters',
                    'body.string' => 'Body must be a string',
                    'user_id.required' => 'User ID is required',
                ],
            );

            if ($validated->fails()) {
                throw new \Exception($validated->errors()->first());
            }

            $result = $validated->validated();

            $notification = Notification::create([
                'title' => $result['title'],
                'body' => $result['body'],
                'user_id' => $result['user_id'],
                'read' => false,
            ]);

            event(new NotificationsEvent($notification, $result['user_id']));
        } catch (\Throwable $th) {
            Log::error(message: 'NotificationController Error: ' . $th->getMessage());
            throw $th;
        }
    }
}
