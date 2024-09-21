<?php

namespace YassineDabbous\FileCast\Helpers;
use Symfony\Component\Mime\MimeTypes;

trait FileHelpers{
    
    protected function isJson($string) {
        //  json_validate() PHP 8.3
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
     }
    
    protected function isBase64URI($value): bool{
        return is_string($value) && preg_match('/^data:(\w+)\/(\w+);base64,/', $value);
    }

    public function guessExtension($mimeType): string
    {
        if (!class_exists(MimeTypes::class)) {
            throw new \LogicException('You cannot guess the extension as the Mime component is not installed. Try running "composer require symfony/mime".');
        }

        if($ext = MimeTypes::getDefault()->getExtensions($mimeType)[0] ?? null){
            return $ext;
        }
        dump('ext not guessed');
        $type = explode('/', $mimeType)[1];
        return str_contains($type, '-') ? $type : collect(explode('-', $type))->last();
    }

}