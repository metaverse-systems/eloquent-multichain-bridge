<?php

namespace MetaverseSystems\EloquentMultiChainBridge\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MetaverseSystems\EloquentMultiChainBridge\EloquentMultiChainBridge;

class DataStreamRegistry extends Model
{
    use HasFactory, SoftDeletes, EloquentMultiChainBridge;

    public $incrementing = false;
    protected $keyType = "string";
    protected $stream = "data-stream-registry";
}
