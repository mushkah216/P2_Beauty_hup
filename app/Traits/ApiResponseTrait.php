<?php
namespace App\Traits;

trait ApiResponseTrait
{
    public function sendResponse($data, $message, $statusCode = 200)
    {
        $response = [
            'success'  => true,
            'data'    => $data,
            'message' => __($message),
            'status_code' => $statusCode,
        ];
        if (is_null($data) || (is_array($data) && empty($data))) {
            unset($response['data']);
        }

        return response()->json($response,$statusCode);
    }

    public function sendError($errorMessage, $statusCode, $data = [])
    {
        $response=[
            'success'  => false,
            'error_message'    => __($errorMessage),
            'status_code'    => $statusCode
        ];
        if (!(is_null($data) || (is_array($data) && empty($data)))) {
            $response['data'] = $data;
        }

        return response()->json($response,$statusCode);
    }
}
