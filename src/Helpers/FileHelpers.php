<?php

namespace YassineDabbous\FileCast\Helpers;

use Symfony\Component\Mime\MimeTypes;
use Illuminate\Support\Arr;

trait FileHelpers{

    function isMultiListArray(array $array): bool{
        if(!count($array)){
            return false;
        }
        if(Arr::isAssoc($array)){
            return false;
        }
        foreach($array as $key => $value){
            if(!is_array($value) || Arr::isAssoc($value)){
                return false;
            }
        }
        return true;
    }
    
    function arrayToCSV(array $input, $delimiter = ',', $enclosure = '"') {
        $result = '';
        foreach($input as $value){
            $result .= implode($delimiter, array_map(fn($v) => "$enclosure$v$enclosure", $value));
            $result .= "\n";
        }
        return trim($result);
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