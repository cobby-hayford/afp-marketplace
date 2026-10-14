<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\AuditService;
use App\Services\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/settings', ['settings' => SiteSetting::orderBy('key')->get()]);
    }

    public function update(Request $r): RedirectResponse
    {
        $data = $r->validate(['settings' => 'required|array', 'settings.*' => 'nullable|string|max:10000']);
        foreach ($data['settings'] as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'text']);
        }SiteSettings::forget();
        AuditService::record('settings.updated', null, ['keys' => array_keys($data['settings'])]);

        return back()->with('success', 'Settings updated.');
    }
}
