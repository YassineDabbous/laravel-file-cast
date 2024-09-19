<?php

namespace YassineDabbous\FileCast;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Str;

class FileCast implements CastsAttributes
{
    public bool $withoutObjectCaching = true;

    protected ?string $disk = null;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?? config('file-cast.disk', 'public');
    }

    
    /**
     * Constructor helper for static typing.
     */
    public static function using(string $disk)
    {
        return static::class.':'.$disk;
    }


    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return $value;
    }

    /**
     * @param  \Illuminate\Http\UploadedFile|null  $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        $this->disk = $this->getDisk($model, $key);
        
        // delete old file if exists
        if(
            config('file-cast.auto_delete', false)
            && isset($attributes[$key])
            && $attributes[$key] != $value
            && Storage::disk($this->disk)->exists($attributes[$key])
            // && (!method_exists($model, 'shouldDeleteFile') || $model->shouldDeleteFile($key, $attributes[$key]))
        ) {
            Storage::disk($this->disk)->delete($attributes[$key]);
        }

        // save file to storage disk
        $folder = config('file-cast.folder') ?? $model?->getTable() ?? 'file_cast_default_path';
        if (is_file($value)) {
            if($value instanceof UploadedFile){
                $value = $value->store($folder, ['disk' => $this->disk]);
            } else {
                $name = collect(explode('/', $value))->last();
                Storage::disk($this->disk)->put("$folder/$name", file_get_contents($value));
                $value = "$folder/$name";
            }
        }
        else if (Str::isUrl($value)) {
            $name = collect(explode('/', $value))->last();
            Storage::disk($this->disk)->put("$folder/$name", file_get_contents($value));
            $value = "$folder/$name";
        }
        else if ($this->isBase64Uri($value)) {
            $ext = explode('/',explode(':',substr($value,0,strpos($value,';')))[1])[1];
            $data = substr($value, strpos($value, ',') + 1);
            $name = uniqid() . '.' . $ext;
            Storage::disk($this->disk)->put("$folder/$name", base64_decode($data));
            $value = "$folder/$name";
        }
        
        return $value;
    }

    
    public function isBase64Uri($value): bool{
        return is_string($value) && preg_match('/^data:(\w+)\/(\w+);base64,/', $value);
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