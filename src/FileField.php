<?php

namespace YassineDabbous\FileCast;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Macroable;

class FileField
{
    use Macroable {
        __call as macroCall;
    }

    public function __construct(
        protected string $value,
        protected Model $model, 
        protected string $key,
        protected ?string $disk = null,
    ){}


    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (method_exists($this, $method)) {
            return $this->{$method}(...$parameters);
        }

        $parameters = [$this->value, ...$parameters];
        return Storage::disk($this->disk)->{$method}(...$parameters);
    }


    public function __toString(): string{
        return $this->value;
    }

}