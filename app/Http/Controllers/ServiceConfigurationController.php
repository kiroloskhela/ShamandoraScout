<?php

namespace App\Http\Controllers;

use App\Support\AppSettings;
use Illuminate\Http\Request;

class ServiceConfigurationController extends Controller
{
    public function edit()
    {
        return view('configurations.edit', [
            'whatsappEnabled' => AppSettings::whatsappEnabled(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'whatsapp_enabled' => ['required', 'in:0,1'],
        ]);

        AppSettings::set('whatsapp_enabled', $data['whatsapp_enabled']);

        $message = $data['whatsapp_enabled'] === '1'
            ? __('WhatsApp is on.')
            : __('WhatsApp is off. Sends are skipped until you turn it on.');

        return redirect()->back()->with('success', $message);
    }
}
