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

	    $this->assertEquals($default, $client->getRoot()->getBody());
	}

	public function testCreatingElement()
	{
		$client = $this->getClient();
	    $q = 'CREATE (u:`User` {name: {name}, email: {email}}) RETURN u';
	    $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com'];
	    $response = $client->sendCypherQuery($q, $params);
	    $result = $response->getResult();


	    $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result',$result);
	    // The user is getting created successfully but never returned afterwards.
	    $this->assertArrayHasKey('User', $result->getNodesByLabel('User', true));
	}

	public function testCreatingElementsAndRelations()
	{
		$client = $this->getClient();
	    $q = 'CREATE (u:`User` {name: {name}, email: {email}})-[:LIKES]->(p:`Post` {title: {title}}) RETURN u, p';
	    $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com', 'title' => 'Sss'];
	    $response = $client->sendCypherQuery($q, $params);
	    $result = $response->getResult();

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
		// You need to recreate the user here because you delete the db on each test run with the query in the setUp() method
		$q = 'CREATE (u:`User` {name: {name}, email: {email}})-[:LIKES]->(p:`Post` {title: {title}}) RETURN u, p';
	  $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com', 'title' => 'Sss'];
	  $client->sendCypherQuery($q, $params);

		$q = 'MATCH (u:`User`) WHERE u.email = {email} RETURN u';

		$response = $client->sendCypherQuery($q, ['email' => 'halawi.abed@gmail.com']);
		$result = $response->getResult();

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

		$result = $client->sendCypherQuery('CREATE (n:Order) RETURN n')->getResult();
		$this->assertTrue(in_array('Order', $client->getLabels()->getBody()));

		$client->renameLabel('Order', 'Product');

		$updated_labels = $client->getLabels()->getBody();
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
