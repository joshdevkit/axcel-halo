<?php

namespace App\Core\Exceptions;

use Exception;

class NativeException extends Exception
{
    public function render()
    {
        $appName = env('APP_NAME', 'Axcel');

        // Custom error message for the user
        $errorMessage = 'Oops! Something went wrong on our end. We are working on fixing this issue. Please try again later.<br>';
        $errorMessage .= 'If the problem persists, please contact our support team with the following details:<br><br>';
        $errorMessage .= '<strong>Email at: </strong>jshpch1996@gmail.com<br>';
        $errorMessage .= 'Thank you for your patience!';

        return '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - ' . htmlspecialchars($appName) . '</title>
            <style>
                body {
                    background-color: #000000;
                    color: #ffffff;
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    text-align: center;
                }
                .app-name {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 30px;
                }
                .error-message {
                    font-size: 18px;
                    max-width: 80%;
                    line-height: 1.5;
                    white-space: pre-wrap;
                    word-wrap: break-word;
                }
            </style>
        </head>
        <body>
            <div class="app-name">' . htmlspecialchars($appName) . '</div>
            <div class="error-message">' . $errorMessage . '</div>
        </body>
        </html>';
    }
}
