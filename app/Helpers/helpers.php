<?php

if (!function_exists('static_asset')) {
    function static_asset($path)
    {
        return asset($path);
    }
}
