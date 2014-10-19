# Trying Out [NeoClient](https://github.com/neoxygen/neo4j-neoclient)

## Installation

- Clone this repo
- `composer install`
- `./vendor/bin/phpunit`

## Questions & Suggestions

- more docs for `setDefaultResultDataContent()`
- standalone `Transaction` instance for dispatched transactions to allow working with the transaction easier than keeping hold of the ID and the Client at all times.
- `sendFormatterCypherQuery()` or any function of your choice to handle formatted response automatically.
- The following code returned an empty array
```php
    $client = new Neoxygen\NeoClient\Client;
    $client->addConnection('default', 'http', '192.168.59.103', 7474)->build();

    $q = 'CREATE (u:`User` {name: "Abed Halawi", email: "halawi.abed@gmail.com"}) RETURN u';
    $response = $client->sendCypherQuery($q);

    $formatter = new Neoxygen\NeoClient\Formatter\ResponseFormatter;

    $result = $formatter->format($response);
    $result->getNodes();
```

- `$node->getProperties()` to accept an array of properties and have only those returned.
- Finding paths between nodes (see https://github.com/jadell/neo4jphp/wiki/Paths) which requires an easy way to work with Neoxygen's Nodes like being able to initialize them dynamically and identifying them by either an attribute or the Node ID.
- An easier way for working with Edges (Relationships) to set properties to them etc. without using Cypher.
- Managing Indexes
- Are batches the same as Transactions according to neoclient ?
- Spatial queries support possibility ?
