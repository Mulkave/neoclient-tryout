<?php

class ClientTest extends PHPUnit_Framework_TestCase {

	public function testConnectivity()
	{
		$client = $this->getClient();

		$default = [
			'management' => 'http://192.168.59.103:7474/db/manage/',
			'data' => 'http://192.168.59.103:7474/db/data/'
		];

	    $this->assertEquals($default, (array) json_decode($client->getRoot()));
	}

	public function testCreatingElement()
	{
		$client = $this->getClient();
	    $q = 'CREATE (u:`User` {name: {name}, email: {email}}) RETURN u';
	    $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com'];
	    $response = $client->sendCypherQuery($q, $params, null, array('graph'));

	    $formatter = $this->getFormatter();
	    $result = $formatter->format($response);
	    $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result', $result);
	    // The user is getting created successfully but never returned afterwards.
	    $this->assertArrayHasKey('User', $result->getNodes());
	}

	public function testFetchingElementByAttribute()
	{
		$client = $this->getClient();
		$q = 'MATCH (u:`User`) WHERE u.email = {email} RETURN u';

		$response = $client->sendCypherQuery($q, ['email' => 'halawi.abed@gmail.com'], null, array('graph'));
		$formatter = $this->getFormatter();
		$result = $formatter->format($response);
		$this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result', $result);
		$this->assertGreaterThan(0, $result->getNodesCount());

		$node = current($result->getNodesByLabel('User'));
		$this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Node', $node);
		$props = $node->getProperties(['name']);
		$this->assertEquals(['name' => 'Abed Halawi'], $props);
	}


	protected function getClient()
	{
		$client = new Neoxygen\NeoClient\Client;
	    $client->addConnection('default', 'http', '192.168.59.103', 7474)->build();
	    return $client;
	}

	protected function getFormatter()
	{
		return new Neoxygen\NeoClient\Formatter\ResponseFormatter;
	}
}
