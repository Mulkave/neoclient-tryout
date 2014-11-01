<?php

use Neoxygen\NeoClient\ClientBuilder;

class ClientTest extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$client = $this->getClient();

		$client->sendCypherQuery('MATCH (n)-[r]-(m) DELETE n,r,m');
		$client->sendCypherQuery('MATCH (n) DELETE n');
	}

	public function testConnectivity()
	{
		$client = $this->getClient();

		$default = [
			'management' => 'http://192.168.59.103:7474/db/manage/',
			'data' => 'http://192.168.59.103:7474/db/data/'
		];

	    $this->assertEquals($default, $client->getRoot());
	}

	public function testCreatingElement()
	{
		$client = $this->getClient();
	    $q = 'CREATE (u:`User` {name: {name}, email: {email}}) RETURN u';
	    $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com'];
	    $result = $client->sendCypherQuery($q, $params);


	    $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result',$result);
	    // The user is getting created successfully but never returned afterwards.
	    $this->assertArrayHasKey('User', $result->getNodesByLabel('User', true));
	}

	public function testCreatingElementsAndRelations()
	{
			$client = $this->getClient();

	    $q = 'CREATE (u:`User` {name: {name}, email: {email}})-[:LIKES]->(p:`Post` {title: {title}}) RETURN u, p';
	    $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com', 'title' => 'Sss'];
	    $result = $client->sendCypherQuery($q, $params);

	    $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result',$result);

	    $nodes = $result->getNodes(['User', 'Post'], true);

	    $this->assertArrayHasKey('User', $nodes);
	    $this->assertArrayHasKey('Post', $nodes);

	    $nodes_too = $result->getNodesByLabels(['User', 'Post'], true);
	    $this->assertArrayHasKey('User', $nodes_too);
	    $this->assertArrayHasKey('Post', $nodes_too);
	}

	public function testFetchingElementByAttribute()
	{
		$client = $this->getClient();
		// Reinsert the user as you delete the db after each test with the setUp() method
		$q = 'MERGE (u:`User` {name: {name}, email: {email}}) RETURN u';
	  $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com'];
	  $client->sendCypherQuery($q, $params);
	  
		$q = 'MATCH (u:`User`) WHERE u.email = {email} RETURN u';

		$result = $client->sendCypherQuery($q, ['email' => 'halawi.abed@gmail.com'], null);

		$this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result', $result);
		$this->assertGreaterThan(0, $result->getNodesCount());

		$node = current($result->getNodesByLabel('User'));

		$this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Node', $node);
		$props = $node->getProperties(['name']);
		$this->assertEquals(['name' => 'Abed Halawi'], $props);
	}

	public function testGettingAndRenamingLabels()
	{
		$client = $this->getClient();

		$result = $client->sendCypherQuery('CREATE (n:Order) RETURN n');
		$this->assertTrue(in_array('Order', $client->getLabels()));

		$client->renameLabel('Order', 'Product');

		$updated_labels = $client->getLabels();
		$this->assertTrue(in_array('Product', $updated_labels));
	}

	public function testIndexing()
	{
		$client = $this->getClient();

		$this->assertTrue($client->createIndex('Person', 'email', 'name'));
	}

	protected function getClient()
	{
		return ClientBuilder::create()
	    ->addConnection('default','http','192.168.59.103',7474)
	    ->setAutoFormatResponse(true)
	    ->build();
	}

}
