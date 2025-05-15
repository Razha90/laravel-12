<?php

namespace App\Http\Middleware;

use App\Models\ClassroomMember;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MemberFromClass
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $classroomId = $request->route('id');
        $cookie = request()->cookie('member-classroom');
        try {
            if (empty($cookie)) {
                $memberClassroom = ClassroomMember::where('classroom_id', $classroomId)
                    ->where('user_id', auth()->user()->id)
                    ->first();
                if ($memberClassroom) {
                    $cookieValue = [];
                    $cookieValue[] = [
                        'classroom_id' => $classroomId,
                        'member_classroom_id' => $memberClassroom->id,
                    ];
                    $encryptedCookieValue = encrypt(json_encode($cookieValue));
                    Cookie::queue('member-classroom', $encryptedCookieValue, 60 * 24 * 30);
                    return $next($request);
                } else {
                    return redirect()->route('not-found')->with('error', __('error.not-member'))->with('code', '4 0 3');
                }
            } else {
                $cookieValue = json_decode(decrypt($cookie), true);
                if ($cookieValue) {
                    $cookieFilter = array_filter($cookieValue, function ($item) use ($classroomId) {
                        return (string) $item['classroom_id'] === (string) $classroomId;
                    });
                    $cookieFilter = array_values($cookieFilter);
                    if (empty($cookieFilter)) {
                        $memberClassroom = ClassroomMember::where('classroom_id', $classroomId)
                            ->where('user_id', auth()->user()->id)
                            ->first();
                        if ($memberClassroom) {
                            $cookieValue[] = [
                                'classroom_id' => $classroomId,
                                'member_classroom_id' => $memberClassroom->id,
                            ];
                            $encryptedCookieValue = encrypt(json_encode($cookieValue));
                            Cookie::queue('member-classroom', $encryptedCookieValue, 60 * 24 * 30);
                            return $next($request);
                        } else {
                            return redirect()->route('not-found')->with('error', __('error.not-member'))->with('code', '4 0 3');
                        }
                    } else {
                        return $next($request);
                    }
                } else {
                    $memberClassroom = ClassroomMember::where('classroom_id', $classroomId)
                        ->where('user_id', auth()->user()->id)
                        ->first();
                    if ($memberClassroom) {
                        $cookieValue = [];
                        $cookieValue[] = [
                            'classroom_id' => $classroomId,
                            'member_classroom_id' => $memberClassroom->id,
                        ];
                        $encryptedCookieValue = encrypt(json_encode($cookieValue));
                        Cookie::queue('member-classroom', $encryptedCookieValue, 60 * 24 * 30);
                        return $next($request);
                    } else {
                        return redirect()->route('not-found')->with('error', __('error.not-member'))->with('code', '4 0 3');
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::error('Error in MemberFromClass middleware: ' . $th->getMessage());
            return redirect()->route('not-found')->with('error', __('error.server_error'))->with('code', '5 0 0');
        }
    }
}
