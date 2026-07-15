<?php

namespace App\Http\Controllers;

use App\Support\AppSettings;
use Illuminate\Http\Request;

class LiveFormSettingsController extends Controller
{
    public function edit()
    {
        return view('liveform-settings.edit', [
            'isOpen' => AppSettings::liveformIsOpen(),
            'closedMessage' => AppSettings::liveformClosedMessage(),
            'updatedAt' => \Illuminate\Support\Facades\DB::table('AppSettings')
                ->where('SettingKey', 'liveform_open')
                ->value('UpdatedAt'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'liveform_open' => 'required|in:0,1',
            'liveform_closed_message' => 'required|string|max:1000',
        ]);

        AppSettings::set('liveform_open', $data['liveform_open']);
        AppSettings::set('liveform_closed_message', $data['liveform_closed_message']);

        return redirect()
            ->route('liveform-settings.edit')
            ->with('status', $data['liveform_open'] === '1'
                ? 'تم فتح باب التسجيل في نموذج الالتحاق.'
                : 'تم إغلاق نموذج الالتحاق. الزوار سيرون صفحة «لا يوجد تسجيل حالياً».');
    }
}
