<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class IntroController extends Controller
{
    public function test()
    {
        return response()->json([
            "status" => "ok",
            "method" => "GET",
        ]);
    }

    public function ip()
    {
        return response()->json([
            "ip" => "127.0.0.1",
        ]);
    }
}
