<?php
namespace Natsu90\Nexmo;

use GuzzleHttp\Client;

abstract class AbstractNexmoClient {

	protected $nexmo_key;
	protected $nexmo_secret;
	protected $end_point;
	protected $client;
	public $error;

	public function __construct($nexmo_key, $nexmo_secret)
	{
		$this->nexmo_key = $nexmo_key;
		$this->nexmo_secret = $nexmo_secret;
		$this->client = new Client(array('base_url' => $this->end_point));
	}
}