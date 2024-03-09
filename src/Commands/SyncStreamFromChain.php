<?php

namespace MetaverseSystems\EloquentMultiChainBridge\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\MultiChain\Facades\MultiChain;
use Exception;
use Illuminate\Support\Facades\DB;

class SyncStreamFromChain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datastream:sync {stream : Name of stream}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restores a database table from its blockchain stream';

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
        $stream = str_replace("-", "", $this->argument('stream'));
        $keys = MultiChain::liststreamkeys($stream);
        foreach($keys as $key)
        {
            $chainItem = $this->getChainItem($stream, $key->key);
            $obj = json_decode($chainItem);
            $newItem = $obj->tableData;
            $dbItem = $this->getDbItem($obj->tableName, $newItem->id);

            if($dbItem == null)
            {
                print "Record ".$newItem->id." in ".$obj->tableName." is not found. Importing from blockchain.\n";
                $this->insertItem($obj->tableName, $newItem);
                continue;
            }

            if($this->compareItems($newItem, $dbItem))
            {
                print "Record ".$newItem->id." in ".$obj->tableName." is up to date.\n";
            }
            else
            {
                print "Record ".$newItem->id." in ".$obj->tableName." is different.\n";
                print "Chain item:\n";
                print_r($newItem);
                print "\nDatabase item: \n";
                print_r($dbItem);
            }
        }

        return 0;
    }

    private function getChainItem($stream, $key)
    {
        $items = MultiChain::liststreamkeyitems($stream, $key, false, 1);
        foreach($items as $item)
        {
            return hex2bin($item->data);
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
