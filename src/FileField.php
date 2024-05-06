<?php

namespace Yaseen\FileCast;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class FileCast implements CastsAttributes
{
    public function __construct(protected ?string $disk = null)
    {
        $this->disk = $this->disk ?? 'public';
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return $value;
    }

    /**
     * @param  \Illuminate\Http\UploadedFile|null  $value
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_file($value)) {
            // delete old file if exists
            if (isset($attributes[$key])) {
                if (Storage::disk($this->disk)->exists($attributes[$key])) {
                    Storage::disk($this->disk)->delete($attributes[$key]);
                }
            }
            $value = $value->store(
                $model?->getTable() ?? 'file_cast_default_path',
                ['disk' => $this->disk]
            );
            return $value;
        } else {
            return $value;
        }
    }
}