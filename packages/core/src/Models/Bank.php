<?php

namespace Shopper\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'code',
        'bin',
        'shortName',
        'logo',
    ];
}
