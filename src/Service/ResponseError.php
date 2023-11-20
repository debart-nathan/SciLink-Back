<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;


class ResponseError
{
    public static function resError(string $message): Response
    {
        $message =[
            'error' => 'Accès refusé'
        ];
        
        return new Response($message[0], Response::HTTP_UNAUTHORIZED);
    }
}