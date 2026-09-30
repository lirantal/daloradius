<?php

/**
 * 2CheckOut Class
 *
 * Integrate the 2CheckOut payment gateway in your site using this easy
 * to use library. Just see the example code to know how you should
 * proceed. Btw, this library does not support the recurring payment
 * system. If you need that, drop me a note and I will send to you.
 *
 * @package     Payment Gateway
 * @category    Library
 * @author      Md Emran Hasan <phpfour@gmail.com>
 * @link        http://www.phpfour.com
 */

include_once ('PaymentGateway.php');

class TwoCo extends PaymentGateway
{
    /**
     * Secret word to be used for IPN verification
     *
     * @var string
     */
    public $secret;

    /**
     * Initialize the 2CheckOut gateway
     *
     * @param none
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Some default values of the class
        $this->gatewayUrl = 'https://www.2checkout.com/checkout/purchase';
        // $this->ipnLogFile = '2co.ipn_results.log';
    }

    /**
     * Enables the test mode
     *
     * @param none
     * @return none
     */
    public function enableTestMode()
    {
        $this->testMode = TRUE;
        $this->addField('demo', 'Y');
    }

    /**
     * Set the secret word
     *
     * @param string the scret word
     * @return void
     */
    public function setSecret($word)
    {
        if (!empty($word))
        {
            $this->secret = $word;
        }
    }

    /**
     * Reject the retired IPN verifier
     *
     * @param none
     * @throws LogicException
     * @deprecated The legacy passback signature does not bind the local order.
     */
    public function validateIpn()
    {
        // Never copy/log the notification or present this check as authorization.
        throw new LogicException('Legacy 2Checkout IPN verification is retired');
    }
}
