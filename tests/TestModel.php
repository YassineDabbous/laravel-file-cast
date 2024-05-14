<?php

namespace YassineDabbous\FileCast\Tests;

use Illuminate\Database\Eloquent\Model;
use YassineDabbous\FileCast\FileCast;
use YassineDabbous\FileCast\HasFileCast;

/**
 * @author Yassine Dabbous <yassine.dabbous@gmail.com>
 */
class TestModel extends Model
{
    use HasFileCast;

    protected $table = 'tests';

    protected $casts = [
        'avatar' => FileCast::class,
    ];

    public function disks() : array {
        return [
            'avatar' => 'fake_disk'
        ];
    }
}