<?php

namespace MetaverseSystems\EloquentMultiChainBridge\Commands;

use Illuminate\Console\Command;
use DB;

class NewBlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datastream:new-block {data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes new block from blockchain';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = $this->argument('data');
        $o = json_decode($data);
        if(!isset($o->vout))
        {
            \Log::info('No vout in object');
        }
        foreach($o->vout as $vout)
        {
            if(!isset($vout->items)) continue;
            foreach($vout->items as $item)
            {
                $data = hex2bin($item->data);
                $o = json_decode($data);
                $record = $o->tableData;
                $query = "SELECT * FROM ".$o->tableName." WHERE `id` = ?";
                $values = DB::select($query, [$record->id]);
                if(count($values))
                {
                    $this->updateItem($o->tableName, $record);
                    \Log::info('Updated item '.$record->id.' in '.$o->tableName.' table');
                }
                else
                {
                    $this->insertItem($o->tableName, $record);
                    \Log::info('Inserted item '.$record->id.' in '.$o->tableName.' table');
                }
            }
        }
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

    private function updateItem($table, $chainItem)
    {
        $values = array();
        $query = "UPDATE $table SET ";
        foreach($chainItem as $k=>$v)
        {
            $query.= $k." = ?, ";
            array_push($values, $v);
        }
        $query = substr($query, 0, -2)." WHERE id = ?";
        array_push($values, $chainItem->id);

        DB::update($query, $values);
    }
}
