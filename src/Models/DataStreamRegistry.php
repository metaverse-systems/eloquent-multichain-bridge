<?php

namespace MetaverseSystems\EloquentMultiChainBridge\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaverseSystems\EloquentMultiChainBridge\EloquentMultiChainBridge;

class DataStreamRegistry extends Model
{
    use HasFactory, EloquentMultiChainBridge;

    protected $stream = "data-stream-registry";
}
