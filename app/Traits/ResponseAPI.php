<?php

namespace App\Traits;

trait ResponseAPI
{
    public function successResponse($data = null, $statusCode = 200)
    {
        return response()->json($data, $statusCode);
    }

    public function errorResponse($errorMessage, $errorCode)
    {
        if (!$errorMessage) {
            return response()->json(['message' => 'Something went wrong!'], 500);
        }

        return response()->json($errorMessage, $errorCode);
    }
}
