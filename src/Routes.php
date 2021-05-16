<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MetaverseSystems\EloquentMultiChainBridge\Controllers\DataStreamRegistryController;

Route::group(['middleware'=>'api', 'prefix'=>'api' ], function () {
    Route::resource('data-stream-registry', DataStreamRegistryController::class);
});
