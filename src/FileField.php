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

    public function toRaw(): string|null {
        return Storage::disk($this->disk)->get( $this->value);
    }
    
    public function toBase64(): string{
        return base64_encode(Storage::disk($this->disk)->get( $this->value));
    }

    public function toArray(): ?array{
        if(str_ends_with($this->value, '.csv')) {
            $stream = fopen($this->path(), 'r');
            $rows = [];
            while (($row = fgetcsv($stream)) !== false) {
                $rows[] = $row;
            }
            fclose($stream);
            return $rows;
            // return str_getcsv($this->get(), PHP_EOL);
        }
        return Storage::disk($this->disk)->json( $this->value);
    }
    
    public function delete(): void
    {
        $this->model->{$this->key} = null;
    }
    
    public function move($to, bool $persist = true): void
    {
        Storage::disk($this->disk)->move($this->value, $to);
        $this->model::unguard();
        $this->model->{$this->key} = "@$to";
        if($persist){
            $this->model->save();
        }
    }

}