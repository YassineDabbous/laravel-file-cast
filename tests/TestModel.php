<?php

namespace YassineDabbous\FileCast\Tests;

use Illuminate\Database\Eloquent\Model;
use YassineDabbous\FileCast\FileCast;
use YassineDabbous\FileCast\HasFileCast;

/**
 * @property null|\YassineDabbous\FileCast\FileField|\Illuminate\Http\Testing\File $avatar
 * 
 * @author Yassine Dabbous <yassine.dabbous@gmail.com>
 */
class TestModel extends Model
{
    use HasFileCast;

    protected $table = 'tests';

    public $timestamps = false;

    protected $casts = [
        'avatar' => FileCast::class,
    ];

    public function disks() : array {
        return [
            'avatar' => 'fake_disk'
        ];
    }
}