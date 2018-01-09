<?php

namespace Omnipay\AuthorizeNet\Message;

use Omnipay\Tests\TestCase;

class CIMCreateCardRequestTest extends TestCase
{
    /** @var CIMCreateCardRequest */
    protected $request;

    public function setUp()
    {
        $this->request = new CIMCreateCardRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'email' => "kaylee@serenity.com",
                'card' => $this->getValidCard(),
                'developerMode' => true
            )
        );
    }

    public function testGetData()
    {
        $data = $this->request->getData();
        $card = $this->getValidCard();
        $this->assertEquals('12345', $data->profile->paymentProfiles->billTo->zip);
        $this->assertEquals($card['number'], $data->profile->paymentProfiles->payment->creditCard->cardNumber);
        $this->assertEquals('testMode', $data->validationMode);
    }

    public function testGetDataShouldHaveCustomBillTo()
    {
        $card = $this->getValidCard();
        unset($card['billingAddress1']);
        unset($card['billingAddress2']);
        unset($card['billingCity']);
        $this->request->initialize(
            array(
                'email' => "kaylee@serenity.com",
                'card' => $card,
                'developerMode' => true,
                'forceCardUpdate' => true,
                'defaultBillTo' => array(
                    'address' => '1234 Test Street',
                    'city' => 'Blacksburg'
                )
            )
        );

        $data = $this->request->getData();
        $this->assertEquals('12345', $data->profile->paymentProfiles->billTo->zip);
        $this->assertEquals('1234 Test Street', $data->profile->paymentProfiles->billTo->address);
        $this->assertEquals('Blacksburg', $data->profile->paymentProfiles->billTo->city);
    }

    public function testValidationModePassedThroughEvenInLivemode()
    {
        $this->request->initialize(
            array(
                'customerProfileId' => '28775801',
                'email' => "kaylee@serenity.com",
		'card' => $this->getValidCard(),
                'developerMode' => false,
            )
        );
        $data = $this->request->getData();
        $this->assertEquals('liveMode', $data->validationMode);

        $this->request->initialize(
            array(
                'customerProfileId' => '28775801',
                'email' => "kaylee@serenity.com",
		'card' => $this->getValidCard(),
                'developerMode' => false,
                'validationMode' => 'testMode',
            )
        );
        $data = $this->request->getData();
        $this->assertEquals('testMode', $data->validationMode);
    }
}
