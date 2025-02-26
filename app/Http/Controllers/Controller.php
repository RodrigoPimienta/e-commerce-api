<?php
namespace App\Http\Controllers;

abstract class Controller
{
    public function response(int $status, bool $error, string $message = '', array | object $data = []): object
    {
        return response()->json([
            'status'  => $status,
            'error'   => $error,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public function randomPassword(): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*'), 0, 10);
    }
}
