<?php

namespace MetaverseSystems\EloquentMultiChainBridge;

use MetaverseSystems\MultiChain\Facades\MultiChain;
use MetaverseSystems\EloquentMultiChainBridge\Models\DataStreamRegistry;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;
use ErrorException;
use Str;
use Auth;

trait EloquentMultiChainBridge
{
    use SoftDeletes;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) 
        {
            $model->id = (string) Str::uuid();
        });

        self::created(function($model)
        {
            self::chainUpdate($model);
        });

        self::updated(function($model)
        {
            self::chainUpdate($model);
        });

        self::deleted(function($model)
        {
            self::chainUpdate($model);
        });
    }

    private static function chainUpdate($model)
    {
        $stream = self::getModelStream(); 
        $user = Auth::user();

        $data = $model->toArray();
        if($user) $data['created_by'] = $user->id;
        else $data['created_by'] = '00000000-0000-0000-0000-000000000000';

        try
        {
            $txid = MultiChain::publish($stream, $data['id'], bin2hex(json_encode($data)));
        }
        catch(Exception $e)
        {
            $message = "Error saving ".get_class(new static())." to $stream.\n";
            $message.= $e->getMessage();
            throw ErrorException($message);
        }
    }

    private static function getModelStream()
    {
        if(isset(self::$stream)) return $self::stream;

        $className = get_class(new static());
        $stream = DataStreamRegistry::where('class_name', $className)->first();
        if(!$stream)
        {
            throw new ErrorException("$className is not registered to a data stream.");
        }
        return $stream->data_stream;
    }
}
