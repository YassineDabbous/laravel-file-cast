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
    
    public function toBase64URI(): string{
        $mime = mime_content_type($this->path());
        return "data:$mime;base64,".$this->toBase64();
    }

    public function json($flags = 0): array|null
    {
        if(method_exists(\Illuminate\Filesystem\FilesystemAdapter::class, 'json')){
            // L >= 10.x
            return Storage::disk($this->disk)->json( $this->value);
        }

        $content = $this->get();

        return is_null($content) ? null : json_decode($content, true, 512, $flags);
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
        return $this->json();
    }
    
    public function delete(bool $persist = FALSE): void
    {
        $this->model->{$this->key} = null;
        if($persist){
            $this->model->save();
        }
    }
    
    public function move($to, bool $persist = FALSE): void
    {
        $this->model->{$this->key} = "@$to";
        if($persist){
            $this->model->save();
        }
    }

}