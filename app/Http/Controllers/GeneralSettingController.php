<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class GeneralSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:general-setting-index')->only('index');
        $this->middleware('permission:general-setting-create')->only(['create', 'store']);
        $this->middleware('permission:general-setting-update')->only(['update', 'update']);
        $this->middleware('permission:general-setting-delete')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('general-settings.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        foreach ($request->types as $type) {
            $value = $request[$type];

            // Update APP_NAME
            if ($type == 'site_name') {
                $this->overWriteEnvFile('APP_NAME', $value);
            }

            // Update APP_TIMEZONE
            if ($type == 'timezone') {
                $this->overWriteEnvFile('APP_TIMEZONE', $value);
            }

            // Upload image/photo/logo
            $lastSegment = strtolower(Str::afterLast($type, '_'));
            if (in_array($lastSegment, ['photo', 'image', 'logo'])) {
                if ($request->hasFile($type)) {
                    // Try to find existing setting
                    $existingSetting = GeneralSetting::where('type', $type)->first();

                    // If it doesn't exist, create a new one
                    if (!$existingSetting) {
                        $existingSetting = new GeneralSetting();
                        $existingSetting->type = $type;
                    }

                    // Delete old file if exists
                    if (!empty($existingSetting->value)) {
                        delete_uploaded_asset($existingSetting->value);
                    }

                    // Upload new file
                    $value = upload_asset($request->file($type));

                    // Assign value
                    $existingSetting->value = is_array($value) ? json_encode($value) : $value;

                    // Save to database
                    $existingSetting->save();
                }
            } else {
                // Save to DB
                $general_settings = GeneralSetting::firstOrNew(['type' => $type]);
                $general_settings->value = is_array($value) ? json_encode($value) : $value;
                $general_settings->save();
            }

            
        }

        Artisan::call('cache:clear');

        return back()->with('success', 'Setting Update Successful');
    }

    public function overWriteEnvFile($type, $val)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            if (!is_numeric($val) && !is_bool($val)) {
                $val = '"' . trim($val) . '"';
            }

            if (strpos($content, $type) !== false) {
                $content = preg_replace(
                    '/^' . $type . '=.*/m',
                    $type . '=' . $val,
                    $content
                );
            } else {
                $content .= "\r\n" . $type . '=' . $val;
            }

            file_put_contents($path, $content);
        }

        return true;
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
