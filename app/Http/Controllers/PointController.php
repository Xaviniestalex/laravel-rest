<?php

namespace App\Http\Controllers;

use App\Models\UserPoint;
use App\Models\UserPointLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    public function addPointByLuckyDraw()
    {
        $userId = Auth::id();
        $lastEarnedDateTime = UserPointLog::where('user_id', $userId)
            ->where('changed_amount', '>', 0)
            ->orderBy('created_at', 'desc')
            ->value('created_at');
        if (date("Y-m-d") === substr($lastEarnedDateTime, 0, 10)) {
            return [
                'fail' => 'You have already joined lucky draw today. Please try it tomorrow.',
            ];
        }

        $originalPoint = UserPoint::findOrFail($userId)->point_earned;
        $addedPoint = rand(10, 100);
        $updatedPoint = $originalPoint + $addedPoint;


        try {
            DB::beginTransaction();
            UserPoint::where('user_id', $userId)->update([
                'point_earned' => $updatedPoint,
            ]);

            $success = UserPointLog::create([
                'user_id' => $userId,
                'changed_amount' => $addedPoint,
            ]);

            DB::commit();
            return [
                'success' => $success,
                'lastEarnedDateTime' => $lastEarnedDateTime,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
