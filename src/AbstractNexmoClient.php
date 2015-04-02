<?php
namespace Natsu90\Nexmo;

use GuzzleHttp\Client;

abstract class AbstractNexmoClient {

	public $nexmo_key;
	public $nexmo_secret;
	public $end_point;
	public $client;
	public $error;

	public function __construct($nexmo_key, $nexmo_secret)
	{
		$this->nexmo_key = $nexmo_key;
		$this->nexmo_secret = $nexmo_secret;
		$this->client = new Client(array('base_url' => $this->end_point));
	}
}