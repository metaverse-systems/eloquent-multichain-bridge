<?php

namespace MetaverseSystems\EloquentMultiChainBridge\Commands;

use Illuminate\Console\Command;
use MetaverseSystems\MultiChain\Facades\MultiChain;
use MetaverseSystems\EloquentMultiChainBridge\Models\DataStreamRegistry;
use Exception;

class RegisterDataStream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datastream:register {stream : Name of stream} {model : Full class name of model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a model to a data stream';

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
        $model = $this->argument('model');

        try
        {
            $streams = MultiChain::liststreams("DataStreamRegistry");
        }
        catch (Exception $e)
        {
            try
            {
                MultiChain::create("stream", "DataStreamRegistry", false);
            }
            catch(Exception $ce)
            {
                print("Could not create stream $stream. ".$ce->getMessage()."\n");
                return -1;
            }
        }

        $streams = MultiChain::liststreams("DataStreamRegistry");
        if(!$streams[0]->subscribed)
        {
            try
            {
                MultiChain::subscribe("DataStreamRegistry");
            }
            catch(Exception $e)
            {
                print("Could not subscribe to stream $stream. ".$e->getMessage()."\n");
                return -1;
            }
        }

        try
        {
            $streams = MultiChain::liststreams($stream);
        }
        catch (Exception $e)
        {
            if($this->confirm("Data stream does not exist, do you want to create it now?"))
            {
                print("Creating data stream: $stream\n");
                try
                {
                    MultiChain::create("stream", $stream, false);
                }
                catch(Exception $ce)
                {
                    print("Could not create stream $stream. ".$ce->getMessage()."\n");
                    return -1;
                }
            }
            else return -1;
        }

        $subscribed = false;
        foreach($streams as $s)
        {
            if($s->name != $stream) continue;
            $subscribed = $s->subscribed;
        }

        if(!$subscribed)
        {
            try
            {
                MultiChain::subscribe($stream);
            }
            catch(Exception $e)
            {
                print("Could not subscribe to stream $stream. ".$e->getMessage()."\n");
                return -1;
            }
            print("Subscribed to stream $stream.\n");
        }

        $dsr = new DataStreamRegistry;
        $dsr->data_stream = $stream;
        $dsr->class_name = $model;
        $dsr->save();

        return 0;
    }
}
