<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Counters 定义数据库model
class Counters extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'count', 'createdAt', 'updateAt',
    ];

    protected $table = 'counts';
    public $timestamps = false;
    protected $primaryKey = 'id';
}
