<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\TextContent;
use App\Models\Translation;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private static $EN_LANGUAGE_ID = 1;
    private static $TC_LANGUAGE_ID = 2;

    public function create(Request $request)
    {
        $request->validate([
            'name_en' => 'required',
            'description_en' => 'required',
            'name_tc' => '',
            'description_tc' => '',
            'quota' => 'required|int',
            'discount' => 'required|int',
            'discount_type' => 'required',
            'required_point' => 'required|int',
        ]);

        $data = $request->all();
        $nameTextContent = TextContent::create([
            'text' => $data['name_en'],
            'language_id' => self::$EN_LANGUAGE_ID,
        ]);
        $name_tx_id = $nameTextContent->id;
        $descriptionTextContent = TextContent::create([
            'text' => $data['description_en'],
            'language_id' => self::$EN_LANGUAGE_ID,
        ]);
        $description_tx_id = $descriptionTextContent->id;

        if (isset($data['name_tc']) && isset($data['description_tc'])) {
            Translation::create([
                'text_content_id' => $name_tx_id,
                'language_id' => self::$TC_LANGUAGE_ID,
                'translation' => $data['name_tc'],
            ]);
            Translation::create([
                'text_content_id' => $description_tx_id,
                'language_id' => self::$TC_LANGUAGE_ID,
                'translation' => $data['description_tc'],
            ]);
            unset($data['name_tc']);
            unset($data['description_tc']);
        }

        unset($data['name_en']);
        unset($data['description_en']);
        $data['name_tx_id'] = $name_tx_id;
        $data['description_tx_id'] = $description_tx_id;

        return Coupon::create($data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name_en' => '',
            'description_en' => '',
            'name_tc' => '',
            'description_tc' => '',
            'quota' => 'int',
            'discount' => 'int',
            'discount_type' => '',
            'required_point' => 'int',
        ]);

        $couponId = request()->id;
        $data = $request->all();

        $name_tx_id = Coupon::findOrFail($couponId)->name_tx_id;
        $description_tx_id = Coupon::findOrFail($couponId)->description_tx_id;

        if (isset($data['name_en'])) {
            TextContent::where('id', $name_tx_id)->update([
                'text' => $data['name_en'],
            ]);
            unset($data['name_en']);
        }
        if (isset($data['description_en'])) {
            TextContent::where('id', $description_tx_id)->update([
                'text' => $data['description_en'],
            ]);
            unset($data['description_en']);
        }
        if (isset($data['name_tc'])) {
            Translation::where('text_content_id', $name_tx_id)->update([
                'translation' => $data['name_tc'],
            ]);
            unset($data['name_tc']);
        }
        if (isset($data['description_tc'])) {
            Translation::where('text_content_id', $description_tx_id)->update([
                'translation' => $data['description_tc'],
            ]);
            unset($data['description_tc']);
        }

        $success = Coupon::where('id', $couponId)->update($data);

        return [
            'success' => $success,
        ];
    }

    public function getAll()
    {
        $coupons = Coupon::all();

        $coupons->each(function ($coupon) {
            $coupon->name = TextContent::findOrFail($coupon->name_tx_id)
                ->where('language_id', self::$EN_LANGUAGE_ID)
                ->value('text');
            $coupon->description = TextContent::findOrFail($coupon->description_tx_id)
                ->where('language_id', self::$EN_LANGUAGE_ID)
                ->value('text');
            unset($coupon->name_tx_id);
            unset($coupon->description_tx_id);
        });

        return $coupons;
    }
}
