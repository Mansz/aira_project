<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BaseApiController extends Controller
{
    protected function inertiaRender($component, $props = [])
    {
        return Inertia::render($component, $props);
    }

    protected function inertiaLocation($url)
    {
        return Inertia::location($url);
    }

    protected function success($data = null, $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function error($message = 'Error', $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
