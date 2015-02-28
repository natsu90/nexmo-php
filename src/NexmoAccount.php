<?php
namespace Natsu90\Nexmo;

use Natsu90\Nexmo\AbstractNexmoClient;
use GuzzleHttp\Client;

class NexmoAccount extends AbstractNexmoClient {

	protected $end_point = 'https://rest.nexmo.com';
    
    public function getBalance()
    {
    	$response = $this->client->get('/account/get-balance/'.$this->nexmo_key.'/'.$this->nexmo_secret);

    	return $response->json()['value'];
    }

    public function getOutboundPricing($countryCode, $prefix = false)
    {

    }

    public function getPhonePricing($phoneNumber, $productCode = 'sms')
    {
    	if(!in_array($productCode, array('sms','voice')))
    		throw new Exception("Invalid product code");
    		
    }

    public function updateAccountSettings($data = array(), $overwrite = false)
    {
        if(!$overwrite) {
            $this->error = array('error' => 'Define moCallBackUrl & drCallBackUrl in array input, otherwise it will read as blank & overwrite. Set $overwrite to true if you want to proceed anyway.');
            return false;
        }

    	$response = $this->client->post('/account/settings/'.$this->nexmo_key.'/'.$this->nexmo_secret, array(
    					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
    					'query' => $data
    				));

    	return $response->json();
    }

    public function topUpAccount($transactionId)
    {
    	$response = $this->client->get('/account/top-up/'.$this->nexmo_key.'/'.$this->nexmo_secret.'/'.$transactionId, array(
    					'exceptions' => false
    				));

    	return $response->getStatusCode() == 200;
    }

    public function getInboundNumbers($data = array())
    {
    	$response = $this->client->get('/account/numbers/'.$this->nexmo_key.'/'.$this->nexmo_secret, array(
    					'query' => $data
    				));

    	return $response->json();
    }

    public function getAvailableInboundNumbers($countryCode, $data = array())
    {
    	$response = $this->client->get('/number/search/'.$this->nexmo_key.'/'.$this->nexmo_secret.'/'.$countryCode, array(
    					'query' => $data,
                        'exceptions' => false,
    				));

        $success = $response->getStatusCode() == 200;
        if(!$success) {
            if($response->getStatusCode() == 420)
                $this->error = array_merge(array('message' => 'Probably invalid ISO country code.'), $response->json());
            else
                $this->error = $response->json();
            return false;
        }

    	return $response->json();
    }

    public function buyNumber($countryCode, $phoneNumber)
    {
    	$response = $this->client->post('/number/buy/'.$this->nexmo_key.'/'.$this->nexmo_secret.'/'.$countryCode.'/'.$phoneNumber, array(
    					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
    					'exceptions' => false
    				));

        $success = $response->getStatusCode() == 200;
        if(!$success)
            $this->error = $response->getStatusCode();

        return $success;
    }

    public function cancelNumber($countryCode, $phoneNumber)
    {
    	$response = $this->client->post('/number/cancel/'.$this->nexmo_key.'/'.$this->nexmo_secret.'/'.$countryCode.'/'.$phoneNumber, array(
    					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
    					'exceptions' => false
    				));

        $success = $response->getStatusCode() == 200;
        if(!$success)
            $this->error = $response->getStatusCode();

        return $success;
    }

    public function updateNumber($countryCode, $phoneNumber, $moHttpUrl, $data = array(), $overwrite = false)
    {
        $data['moHttpUrl'] = $moHttpUrl;
    	$response = $this->client->post('/number/update/'.$this->nexmo_key.'/'.$this->nexmo_secret.'/'.strtoupper($countryCode).'/'.$phoneNumber, array(
    					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
    					'query' => $data,
    					'exceptions' => false
    				));

        $success = $response->getStatusCode() == 200;
        if(!$success)
            $this->error = $response->json();

    	return $success;
    }
}
