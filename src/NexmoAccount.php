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

    private function validateOriginator($inp){
        // Remove any invalid characters
        $ret = preg_replace('/[^a-zA-Z0-9]/', '', (string)$inp);
        if(preg_match('/[a-zA-Z]/', $inp)){
            // Alphanumeric format so make sure it's < 11 chars
            $ret = substr($ret, 0, 11);
        } else {
            // Numerical, remove any prepending '00'
            if(substr($ret, 0, 2) == '00'){
                $ret = substr($ret, 2);
                $ret = substr($ret, 0, 15);
            }
        }
        
        return (string)$ret;
    }

    public function sendMessage($from, $to, $text, $data = array())
    {
        // Making sure strings are UTF-8 encoded
        if ( !is_numeric($from) && !mb_check_encoding($from, 'UTF-8') ) {
            $this->error = array('error' => '$from needs to be a valid UTF-8 encoded string');
            return false;
        }
        if ( !mb_check_encoding($text, 'UTF-8') ) {
            $this->error = array('error' => '$message needs to be a valid UTF-8 encoded string');
            return false;
        }

        $isUnicode = max(array_map('ord', str_split($text))) > 127;

        // Make sure $from is valid
        $from = $this->validateOriginator($from);

        $body = array(
                        'api_key' => $this->nexmo_key,
                        'api_secret' => $this->nexmo_secret,
                        'from' => $from,
                        'to' => $to,
                        'text' => $text,
                        'type' => $isUnicode ? 'unicode' : 'text'
                    );

        $body = array_merge($data, $body);

        $response = $this->client->post('/sms/json', array(
                'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
                'body' => $body
            ));

        return $response->json();
    }

    public function getOutboundPricing($countryCode, $prefix = false)
    {
        return true;
    }

    public function getPhonePricing($phoneNumber, $productCode = 'sms')
    {
        if(!in_array($productCode, array('sms','voice')))
            throw new Exception("Invalid product code");
        return true;
    }

    /*
        Define moCallBackUrl & drCallBackUrl in array input, otherwise it will read as blank & get overwritten.
    */
    public function updateAccountSettings($data = array())
    {
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
            $this->error = $response->getBody();

        return $success;
    }
}
