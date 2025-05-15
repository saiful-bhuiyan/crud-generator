<?php

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Storage;

if (!function_exists('static_asset')) {
    function static_asset($path)
    {
        return asset($path);
    }
}

if (!function_exists('upload_asset')) {
    /**
     * Upload an image and return its public asset URL.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @param string|null $disk
     * @return string
     */
    function upload_asset($file, $directory = 'uploads', $disk = 'public')
    {
        if (!$file || !$file->isValid()) {
            return '';
        }

        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, $disk);

        return Storage::disk($disk)->url($path);
    }
}

if (!function_exists('get_uploaded_asset')) {
    /**
     * Get the public URL for an uploaded asset.
     *
     * @param string $path
     * @param string $disk
     * @return string
     */
    function get_uploaded_asset($path, $disk = 'public')
    {
        if (!$path) {
            return '';
        }
        return Storage::disk($disk)->url($path);
    }
}

if (!function_exists('delete_uploaded_asset')) {
    /**
     * Delete an uploaded asset from storage.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    function delete_uploaded_asset($path, $disk = 'public')
    {
        if (!$path) {
            return false;
        }
        return Storage::disk($disk)->delete($path);
    }
}

if(!function_exists('getGeneralSetting')) {
    function getGeneralSetting($type) {
        $setting = GeneralSetting::where('type',$type)->first();
        return $setting->value ?? null;
    }
}
