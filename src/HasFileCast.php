<?php

namespace YassineDabbous\FileCast;

use Illuminate\Database\Eloquent\Model;

trait HasFileCast
{
    public static function bootHasFileCast(){
        static::deleted(function(Model $model){
            $casts = $model->getCasts();
            foreach ($casts as $key => $value) {
                if($value === FileCast::class){
                    $model->$key = null;
                }
            }
        });
    }
}