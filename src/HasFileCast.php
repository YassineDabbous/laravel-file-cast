<?php

namespace YassineDabbous\FileCast;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasFileCast
{
    public static function bootHasFileCast(){
        static::deleted(function(Model $model){
            $casts = $model->getCasts();
            foreach ($casts as $key => $value) {
                if(Str::startsWith($value, FileCast::class) ){
                    $model->$key = null;
                }
            }
        });
    }
}