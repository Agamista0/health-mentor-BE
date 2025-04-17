<?php

namespace App\Response;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $data;

    /**
     * ApiResponse constructor.
     *
     * @param int $status
     * @param string $message
     * @param array $data
     */
    public function __construct(int $status, string $message, array $data = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Send the API response
     *
     * @return JsonResponse
     */
    public function send(): JsonResponse
    {
        return response()->json([
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
        ], $this->status);
    }

    /**
     * Return a success response
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($message, $data = null, $statusCode = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return an error response
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message, $errors = null, $statusCode = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
