<?php

namespace YassineDabbous\FileCast;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class FileCast implements CastsAttributes
{
    public function __construct(protected ?string $disk = null)
    {
        $this->disk = config('file-cast.disk', 'public');
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
        if (is_file($value)) {
            $this->disk = $this->getDisk($model, $key);
            
            // delete old file if exists
            if (isset($attributes[$key])) {
                if (Storage::disk($this->disk)->exists($attributes[$key])) {
                    Storage::disk($this->disk)->delete($attributes[$key]);
                }
            }
            
            $folder = config('file-cast.folder') ?? $model?->getTable() ?? 'file_cast_default_path';
            
            if($value instanceof UploadedFile){
                $value = $value->store($folder, ['disk' => $this->disk]);
            }
            else {
                $name = collect(explode('/', $value))->last();
                Storage::disk($this->disk)->put("$folder/$name", file_get_contents($value));
                $value = "$folder/$name";
            }
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