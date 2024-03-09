<?php

namespace MetaverseSystems\EloquentMultiChainBridge\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\MultiChain\Facades\MultiChain;
use Exception;
use Illuminate\Support\Facades\DB;

class SyncChainFromModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datastream:record {model : Name of model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Records a database table to a blockchain stream';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $model = $this->argument('model');
        $records = $model::get();
        $stream = with(new $model)->getModelStream();
        $table = with(new $model)->getTable();
        foreach($records as $record)
        {
            $chainItem = $this->getChainItem($stream, $record->id);
            if(!$chainItem)
            {
                $model::chainUpdate($record);
                continue;
            }

            $dbItem = $this->getDbItem($table, $record->id);
            if(!$this->compareItems($chainItem, $dbItem))
            {
                $model::chainUpdate($record);
            }
        }

        return 0;
    }

    private function getChainItem($stream, $key)
    {
        $items = MultiChain::liststreamkeyitems($stream, $key, false, 1);
        foreach($items as $item)
        {
            return json_decode(hex2bin($item->data))->tableData;
        }
    }

    private function getDbItem($table, $id)
    {
        $query = "SELECT * FROM `$table` WHERE `id` = ?";
        $values = DB::select($query, [$id]);
        if(count($values)) return $values[0];

        return null;
    }

    private function compareItems($chainItem, $dbItem)
    {
        foreach($chainItem as $k=>$v)
        {
            if($dbItem->$k != $v) return false;
        }

        return true;
    }

    private function insertItem($table, $chainItem)
    {
        $values = array();
        $query = "INSERT INTO $table (";
        foreach($chainItem as $k=>$v)
        {
            $query.= $k.", ";
        }
        $query = substr($query, 0, -2).") VALUES (";
        foreach($chainItem as $k=>$v)
        {
            $query.= "?, ";
            array_push($values, $v);
        }
        $query = substr($query, 0, -2).")";

        DB::insert($query, $values);
    }
}
