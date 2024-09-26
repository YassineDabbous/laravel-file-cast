<?php

namespace YassineDabbous\FileCast;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use YassineDabbous\FileCast\Helpers\FileHelpers;

class FileCast implements CastsAttributes
{
    use FileHelpers;

    public bool $withoutObjectCaching = true;

    protected ?string $disk = null;
    protected ?string $default = null;

    public function __construct(?string $disk = null, ?string $default = null)
    {
        $this->disk = $disk ?? config('file-cast.disk', 'public');
        $this->default = $default ?? config('file-cast.default');
    }


    /** Constructor helper for static typing. */
    public static function using(?string $disk = null, ?string $default = null)
    {
        return static::class.':'.implode(',', array_filter([$disk, $default]));
    }


    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        $this->disk = $this->getDisk($model, $key);
        if(!$v = $value ?? $this->default){
            return null;
        }
        return new FileField(value: $v, model: $model, key: $key,disk: $this->disk);
    }

    
    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        $this->disk = $this->getDisk($model, $key);
        
        /** Change file path without copying it. */
        if(is_string($value) && str_starts_with($value,'@')) {
            $value = str_replace('@', '', $value);
            Storage::disk($this->disk)->move($attributes[$key], $value);
            return $value;
        }

        // Delete old file if exists
        if(
            config()->boolean('file-cast.auto_delete', false)
            && isset($attributes[$key])
            && $attributes[$key] != $value
            && Storage::disk($this->disk)->exists($attributes[$key])
        ) {
            Storage::disk($this->disk)->delete($attributes[$key]);
        }

        // Save file to storage disk
        $folder = config('file-cast.folder') ?? $model?->getTable() ?? 'file_cast_default_path';
        
        if (is_array($value)) {
            if($this->isMultiListArray($value)){
                $name = uniqid() . '.csv';
                $value = $this->arrayToCSV($value);
            } else {
                $name = uniqid() . '.json';
                $value = json_encode($value);
            }
            Storage::disk($this->disk)->put("$folder/$name", $value);
            $value = "$folder/$name";
        }
        else if (is_file($value)) {
            if($value instanceof UploadedFile){
                $value = $value->store($folder, ['disk' => $this->disk]);
            } else {
                $name = collect(explode('/', $value))->last();
                Storage::disk($this->disk)->put("$folder/$name", file_get_contents($value));
                $value = "$folder/$name";
            }
        }
        else if (Str::isUrl($value)) {
            $response = Http::get($value);
            $response->throw();
            $name = uniqid() . '.'. $this->guessExtension($response->header('Content-Type'));
            Storage::disk($this->disk)->put("$folder/$name", $response->body());
            $value = "$folder/$name";
        }
        else if ($this->isBase64Uri($value)) {
            $ext = explode('/',explode(':',substr($value,0,strpos($value,';')))[1])[1];
            $data = substr($value, strpos($value, ',') + 1);
            $name = uniqid() . '.' . $ext;
            Storage::disk($this->disk)->put("$folder/$name", base64_decode($data));
            $value = "$folder/$name";
        }
        else if (Str::isJson($value)) {
            $name = uniqid() . '.json';
            Storage::disk($this->disk)->put("$folder/$name", $value);
            $value = "$folder/$name";
        }

        return $value;
    }



    /** Get a per-column disk */
    public function getDisk(Model $model, string $key){
        $disks = [];
        if(method_exists($model, 'disks')){
            $disks = $model->disks();
        }
        else if(property_exists($model, 'disks')){
            $disks = $model->disks;
        }

        if(isset($disks[$key])){
            return $disks[$key];
        }

        return $this->disk;
    }
}