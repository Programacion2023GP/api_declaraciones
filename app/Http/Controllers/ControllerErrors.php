<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;



class ControllerErrors extends Controller
{
    public function handleException($logFile, Exception $exception)
    {
        $logDirectory = storage_path('errors');

        if (!is_dir($logDirectory)) {
            if (!mkdir($logDirectory, 0777, true) && !is_dir($logDirectory)) {
            }
        }
        $logFilePath = $logDirectory . '/' . $logFile . '.log';
        $errorMessage = '[' . now() . '] ' . $exception->getMessage() . PHP_EOL;
        if (file_put_contents($logFilePath, $errorMessage, FILE_APPEND) === false) {
        }
    }
    
}
