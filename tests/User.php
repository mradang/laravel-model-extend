<?php

namespace mradang\LaravelModelExtend\Test;

use Illuminate\Database\Eloquent\Model;
use mradang\LaravelModelExtend\Traits\ModelChangeMessageTrait;

class User extends Model
{
    use ModelChangeMessageTrait;

    protected $fillable = ['name', 'age', 'titles'];

    protected $casts = [
        'titles' => 'array',
    ];
}
