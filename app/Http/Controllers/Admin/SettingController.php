<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(): View
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = Setting::all();

        foreach ($settings as $setting) {
            $value = $request->input($setting->key);

            if ($setting->type === 'boolean') {
                $value = $request->has($setting->key) ? '1' : '0';
            }

            if ($value !== null) {
                $setting->update(['value' => $value]);
            }
        }

        $this->audit->log('updated', 'settings', null, 'Application Settings');

        return back()->with('success', 'Settings saved.');
    }
}
