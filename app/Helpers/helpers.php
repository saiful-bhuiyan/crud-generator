<?php

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

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

        // Store file
        $path = $file->storeAs($directory, $filename, $disk);

        // ✅ Return relative path (e.g. "uploads/abc123.jpg")
        return $path;
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


function getGeneralSetting($type) {
    $setting = GeneralSetting::where('type',$type)->first();
    return $setting->value ?? null;
}


function formatDate($date)
{
    if(!$date) {
        return null;
    }

    $generalDateFormat = getGeneralSetting('date_format');
    $allowedFormats = ['d-m-Y', 'Y-m-d','d/m/Y','Y/m/d','d M Y', 'Y M d'];
    if(in_array($generalDateFormat, $allowedFormats)) {
        $format = $generalDateFormat;
    } else if(!$date) {
        $format = 'd-m-Y';
    }

    return $date ? \Carbon\Carbon::parse($date)->format($format) : null;
}


function formatDateTime($date)
{
    if(!$date) {
        return null;
    }

    $generalDateFormat = getGeneralSetting('date_format');
    $allowedFormats = ['d-m-Y', 'Y-m-d','d/m/Y','Y/m/d','d M Y', 'Y M d'];
    if(in_array($generalDateFormat, $allowedFormats)) {
        $format = $generalDateFormat . ' h:i A';
    } else {
        $format = 'd-m-Y h:i A';
    }

    return $date ? \Carbon\Carbon::parse($date)->format($format) : null;
}


function formatDateRange($daterange) // eg : 2025-10-12 to 2025-10-13
{
    if(!$daterange) {
        return null;
    }

    $dates = explode(' to ', $daterange);
    if(count($dates) != 2) {
        return null;
    }

    $startDate = formatDate($dates[0]);
    $endDate = formatDate($dates[1]);

    return $startDate . ' to ' . $endDate;
}

function formatSqlDateTime($date)
{
    return $date ? \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s') : null;
}

function formatCurrency($amount)
{
    $currencySymbol = getGeneralSetting('currency_symbol') ?? '$';
    $currencyPosition = getGeneralSetting('currency_position') ?? 'left';
    $currencyDecimal = getGeneralSetting('currency_decimal') ?? 2;

    if ($currencyPosition === 'left') {
        return $currencySymbol . number_format($amount, $currencyDecimal);
    } else {
        return number_format($amount, $currencyDecimal) . ' ' . $currencySymbol;
    }
}

function createMpdfInstance(string $format = 'A4', string $orientation = 'L'): Mpdf
{
    $defaultConfig = (new ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    return new Mpdf([
        'format'        => $format . '-' . strtoupper($orientation),
        'margin_top'    => 10,
        'margin_bottom' => 10,
        'margin_left'   => 10,
        'margin_right'  => 10,

        // ✅ Correct font directory
        'fontDir' => array_merge($fontDirs, [
            public_path('assets/fonts'),
        ]),

        // ✅ Register Bangla font (key name should match the font family)
        'fontdata' => $fontData + [
            'siyamrupali' => [ // 👈 rename the key to match the actual font name
                'R' => 'SiyamRupali.ttf', // 👈 filename must match exactly (case-sensitive)
                'useOTL' => 0xFF,         // complex text rendering for Bangla
                'useKashida' => 75,
            ],
        ],

        // ✅ Default font
        'default_font' => 'siyamrupali',
    ]);
}
