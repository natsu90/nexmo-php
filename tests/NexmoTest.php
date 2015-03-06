<?php
namespace Natsu90\Nexmo\Tests;

use Natsu90\Nexmo\NexmoAccount;
use Dotenv;
use Namshi\Cuzzle\Formatter\CurlFormatter;

class NexmoTest extends \PHPUnit_Framework_TestCase
{
    private $nexmo;
    private $debug = true;
    private $callBackUrl = 'http://1296279f.ngrok.com';

    protected function setUp()
    {
        Dotenv::load(dirname(__DIR__));
        Dotenv::required(array('NEXMO_KEY', 'NEXMO_SECRET'));
        $this->nexmo = new NexmoAccount(getenv('NEXMO_KEY'), getenv('NEXMO_SECRET'));
    }

    public function testGetBalance()
    {
        $balance = $this->nexmo->getBalance();
        echo $balance;
        $this->assertTrue(is_numeric($balance));
    }

    public function sendMessageDataProvider()
    {
        return array(
            array('601117225067', '60122862306', 'hello world'),
            array('60327884488', '60122862306', 'الله أكبر'),
        );
    }

    /**
     * @dataProvider sendMessageDataProvider
     */

    public function testSendMessage($from, $to, $text)
    {
        $response = $this->nexmo->sendMessage($from, $to, $text);
        print_r($response);
        $this->assertTrue((bool) $response);
    }

    /**
     * @dataProvider updateAccountSettingsDataProvider
     */

    public function testUpdateAccountSettings(array $data)
    {
        $response = $this->nexmo->updateAccountSettings($data, true);
        print_r($response);
        print_r($this->nexmo->error);
        $this->assertTrue((bool) $response);
    }

    public function updateAccountSettingsDataProvider()
    {
        return array(
            array(array('moCallBackUrl' => $this->callBackUrl)),
            array(array('drCallBackUrl' => $this->callBackUrl)),
            array(array('moCallBackUrl' => $this->callBackUrl, 'drCallBackUrl' => $this->callBackUrl)),
            array(array())
        );
    }

    /**
     * @dataProvider getInboundNumbersDataProvider
     */

    public function testGetInboundNumbers(array $data)
    {
        $response = $this->nexmo->getInboundNumbers($data);
        print_r($response);
        $this->assertTrue(isset($response['count']) && isset($response['numbers']) && is_numeric($response['count']) && is_array($response['numbers']));
        return $response['numbers'];
    }

    public function getInboundNumbersDataProvider()
    {
        return array(
            array(array('pattern' => 601)),
            array(array())
        );
    }

    public function updateNumberDataProvider()
    {
        return array(
            array(array(), array('voiceStatusCallback' => $this->callBackUrl)),
            array(array(), array())
        );
    }

    /**
    *@dataProvider updateNumberDataProvider
    */

    public function testUpdateNumber(array $inboundNumParams, array $testNumParams)
    {
        $numbers = $this->testGetInboundNumbers($inboundNumParams);
        if(!$numbers)
            return;
        foreach($numbers as $number)
        {
            $response = $this->nexmo->updateNumber($number['country'], $number['msisdn'], $this->callBackUrl, $testNumParams);
            $this->assertTrue($response);
            print_r($this->nexmo->error);
        }
    }

    /**
    * @dataProvider searchNumberDataProvider
    */
    public function testSearhNumber($country, $inboundNumParams)
    {
        $response = $this->nexmo->getAvailableInboundNumbers($country, $inboundNumParams);
        print_r($this->nexmo->error);
        $this->assertTrue((bool) $response);
        print_r($response);
        return $response['numbers'];
    }

    public function searchNumberDataProvider()
    {
        return array(
            array('MY', array('features' => 'SMS,VOICE', 'size' => 3)),
            array('GB', array('features' => 'SMS,VOICE', 'size' => 3))
        );
    }

    public function testBuyNumber()
    {
        $number = $this->testSearhNumber('GB', array('features' => 'SMS,VOICE'))[0];
        $response = $this->nexmo->buyNumber($number['country'], $number['msisdn']);
        print_r($this->nexmo->error);
        $this->assertTrue($response);
        return $number;
    }

    /**
    * @depends testBuyNumber
    */

    public function testCancelNumber($number)
    {
        $response = $this->nexmo->cancelNumber($number['country'], $number['msisdn']);
        print_r($this->nexmo->error);
        $this->assertTrue($response);
    }

    protected function tearDown()
    {
        $this->nexmo = null;
    }
}
