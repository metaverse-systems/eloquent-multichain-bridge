# metaverse-systems/eloquent-multichain-bridge

Connect Eloquent ORM to MultiChain

* Install

```
composer require metaverse-systems/eloquent-multichain-bridge
```

* Make sure your migration includes: a UUID for the primary key, timestamps, and softDeletes

```
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->string("from");
            $table->string("to");
            $table->string("body");
        });
```

* Add trait to your model

```
use MetaverseSystems\EloquentMultiChainBridge\EloquentMultiChainBridge;

class Message extends Model
{
    use HasFactory, EloquentMultiChainBridge;
}
```

Now when a Message is created, updated, or deleted, a copy will be replicated on the blockchain.
