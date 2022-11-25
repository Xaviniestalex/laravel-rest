<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Models\UserPoint;
use App\Models\UserPointLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RedeemController extends Controller
{
    public function create(Request $request)
    {
        // $request->validate([
        //     'coupon_id' => 'required|int',
        // ]);

        $data = $request->all();
        $couponId = $data['coupon_id'];

        $quota = Coupon::findOrFail($couponId)->quota;
        if ($quota === 0) {
            return [
                'failed' => 'No quota left for this coupon.',
            ];
        }

        $userId = Auth::id();
        $point_earned = UserPoint::where('user_id', $userId)->value('point_earned');
        $point_used = UserPoint::where('user_id', $userId)->value('point_used');
        $point_left = $point_earned - $point_used;

        $required_point = Coupon::findOrFail($couponId)->required_point;
        if ($point_left < $required_point) {
            return [
                'failed' => 'Insufficient point to redeem this coupon.',
            ];
        }

        $updated_quota = $quota - 1;
        DB::beginTransaction();

        try {
            Coupon::where('id', $couponId)->update([
                'quota' => $updated_quota,
            ]);

            UserCoupon::create([
                'user_id' => $userId,
                'coupon_id' => $couponId,
                'obtained_at' => date("Y-m-d H:i:s"),
            ]);

            $updated_point_used = $point_used + $required_point;
            UserPoint::where('user_id', $userId)->update([
                'point_used' => $updated_point_used,
            ]);

            UserPointLog::create([
                'user_id' => $userId,
                'changed_amount' => - ($required_point),
            ]);

            DB::commit();

            $updated_point_left = $point_left - $required_point;
            return [
                'success' => "This coupon is redeemed! You now have ${updated_point_left} point(s)."
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
