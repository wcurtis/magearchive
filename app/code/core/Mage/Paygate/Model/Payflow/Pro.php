<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Paygate
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payflow Pro payment gateway model
 *
 * @category    Mage
 * @package     Mage_Paygate
 */

class Mage_Paygate_Model_Payflow_Pro extends  Mage_Payment_Model_Method_Cc
{
    const TRXTYPE_AUTH_ONLY         = 'A';
    const TRXTYPE_SALE              = 'S';
    const TRXTYPE_CREDIT            = 'C';
    const TRXTYPE_DELAYED_CAPTURE   = 'D';
    const TRXTYPE_DELAYED_VOID      = 'V';
    const TRXTYPE_DELAYED_VOICE     = 'F';
    const TRXTYPE_DELAYED_INQUIRY   = 'I';

    const TENDER_AUTOMATED          = 'A';
    const TENDER_CC                 = 'C';
    const TENDER_PINLESS_DEBIT      = 'D';
    const TENDER_ECHEK              = 'E';
    const TENDER_TELECHECK          = 'K';
    const TENDER_PAYPAL             = 'P';

    const RESPONSE_DELIM_CHAR = ',';

    protected $_clientTimeout = 45;

    const RESPONSE_CODE_APPROVED = 0;
    const RESPONSE_CODE_DECLINED = 12;
    const RESPONSE_CODE_CAPTURE_ERROR = 111;

    protected $_code = 'verisign';

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;

    /*
    * 3 = Authorisation approved
    * 6 = Settlement pending (transaction is scheduled to be settled)
    * 9 =  Authorisation captured
    */
    protected $_validVoidTransState = array(3,6,9);

    public function authorize(Varien_Object $payment, $amount)
    {
        if($amount>0){
            $payment->setTrxtype(self::TRXTYPE_AUTH_ONLY);
            $payment->setAmount($amount);
            $request = $this->buildRequest($payment);
            $result = $this->postRequest($request);
            $payment->setCcTransId($result->getPnref());
            switch ($result->getResultCode()){
                case self::RESPONSE_CODE_APPROVED:
                     $payment->setStatus('APPROVED');
                     $payment->setPaymentStatus('AUTHORIZE');
                     break;
                default:
                    Mage::throwException($result->getRespmsg()?$result->getRespmsg():Mage::helper('paygate')->__('Error in authorizing the payment'));
                break;
            }
        }else{
            Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization'));
        }
        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        if ($payment->getCcTransId()) {
             $payment->setTrxtype(self::TRXTYPE_DELAYED_CAPTURE);
              $request = $this->buildBasicRequest($payment);
        } else {
             $payment->setTrxtype(self::TRXTYPE_SALE);
             $request = $this->buildRequest($payment);
        }

         if($amount>0){
             $request->setAmt($amount);
         }
         $result = $this->postRequest($request);
         if($result->getResultCode()!=self::RESPONSE_CODE_APPROVED){
             /*
             * payflow: only one delayed capture transaction is allower per authorization.
                        so need to use sale transaction
             */
             Mage::throwException($result->getRespmsg()?$result->getRespmsg():Mage::helper('paygate')->__('Error in capturing the payment'));
         }else{
             $payment->setStatus('APPROVED');
             $payment->setPaymentStatus('CAPTURE');
             $payment->setCcTransId($result->getPnref());
         }
        return $this;
    }

    public function postRequest(Varien_Object $request)
    {
        if ($this->getConfigData('debug')) {
            foreach( $request->getData() as $key => $value ) {
                $value = (string)$value;
                $requestData[] = strtoupper($key) . '[' . strlen($value) . ']=' . $value;
            }

            $requestData = join('&', $requestData);

            $debug = Mage::getModel('paygate/authorizenet_debug')
                ->setRequestBody($requestData)
                ->setRequestSerialized(serialize($request->getData()))
                ->setRequestDump(print_r($request->getData(),1))
                ->save();
        }

        $client = new Varien_Http_Client();

        $uri = $this->getConfigData('url');
        $client->setUri($uri)
               ->setConfig(array(
                    'maxredirects'=>5,
                    'timeout'=>30,
                ))
            ->setMethod(Zend_Http_Client::POST)
            ->setParameterPost($request->getData())
            ->setHeaders('X-VPS-VIT-CLIENT-CERTIFICATION-ID: 33baf5893fc2123d8b191d2d011b7fdc')
            ->setHeaders('X-VPS-Request-ID: ' . $request->getRequestId())
            ->setHeaders('X-VPS-CLIENT-TIMEOUT: ' . $this->_clientTimeout)
        ;

        $response = $client->request();

        $result = Mage::getModel('paygate/payflow_pro_result');

        $response = strstr($response->getBody(), 'RESULT');
        $valArray = explode('&', $response);

        foreach($valArray as $val) {
                $valArray2 = explode('=', $val);
                $result->setData(strtolower($valArray2[0]), $valArray2[1]);
        }

        $result->setResultCode($result->getResult())
                ->setRespmsg($result->getRespmsg());

        if (!empty($debug)) {
            $debug
                ->setResponseBody($response)
                ->setResultSerialized(serialize($result->getData()))
                ->setResultDump(print_r($result->getData(),1))
                ->save();
        }

        return $result;
    }

    public function buildRequest(Varien_Object $payment)
    {
        $document = $payment->getDocument();

        if( !$payment->getTrxtype() ) {
            $payment->setTrxtype(self::TRXTYPE_AUTH_ONLY);
        }

        if( !$payment->getTender() ) {
            $payment->setTender(self::TENDER_CC);
        }

        $request = Mage::getModel('paygate/payflow_pro_request')
            ->setUser($this->getConfigData('user'))
            ->setVendor($this->getConfigData('vendor'))
            ->setPartner($this->getConfigData('partner'))
            ->setPwd($this->getConfigData('pwd'))
            ->setTender($payment->getTender())
            ->setTrxtype($payment->getTrxtype())
            ->setVerbosity($this->getConfigData('verbosity'))
            ->setRequestId($this->_generateRequestId())
            ;

        if($payment->getAmount()){
            $request->setAmt(round($payment->getAmount(),2));
        }

        switch ($request->getTender()) {
            case self::TENDER_CC:
                    if($payment->getCcNumber()){
                        $request->setComment1($payment->getCcOwner())
                            ->setAcct($payment->getCcNumber())
                            ->setExpdate(sprintf('%02d',$payment->getCcExpMonth()).substr($payment->getCcExpYear(),-2,2))
                            ->setCvv2($payment->getCcCid());
                    }
                break;
        }

        $order = $payment->getOrder();
        if(!empty($order)){
            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $request->setFirstName($billing->getFirstname())
                    ->setLastName($billing->getLastname())
                    ->setStreet($billing->getStreet(1))
                    ->setCity($billing->getCity())
                    ->setState($billing->getRegion())
                    ->setZip($billing->getPostcode())
                    ->setCountry($billing->getCountry())
                    ->setEmail($payment->getOrder()->getCustomerEmail());
            }
        }
        return $request;
    }

    protected function _generateRequestId()
    {
        return md5(microtime() . rand(0, time()));
    }

     /**
      * buildBasicRequest
      *
      * @access public
      * @return object which was set with all basic required information
      */
    public function buildBasicRequest(Varien_Object $payment)
    {
        if( !$payment->getTender() ) {
            $payment->setTender(self::TENDER_CC);
        }

        $request = Mage::getModel('paygate/payflow_pro_request')
                ->setUser($this->getConfigData('user'))
                ->setVendor($this->getConfigData('vendor'))
                ->setPartner($this->getConfigData('partner'))
                ->setPwd($this->getConfigData('pwd'))
                ->setTender($payment->getTender())
                ->setTrxtype($payment->getTrxtype())
                ->setVerbosity($this->getConfigData('verbosity'))
                ->setRequestId($this->_generateRequestId())
                ->setOrigid($payment->getCcTransId());
        return $request;
    }

    /**
      * canVoid
      *
      * @access public
      * @param string $payment Varien_Object object
      * @return Mage_Payment_Model_Abstract
      * @desc checking the transaction id is valid or not and transction id was not settled
      */
    public function canVoid(Varien_Object $payment)
    {
        if($payment->getCcTransId()){
            $payment->setTrxtype(self::TRXTYPE_DELAYED_INQUIRY);

            $request=$this->buildBasicRequest($payment);
            $result = $this->postRequest($request);
            if ($this->getConfigData('debug')) {
              $payment->setCcDebugRequestBody($result->getRequestBody())
                ->setCcDebugResponseSerialized(serialize($result));
            }

            if($result->getResultCode()==self::RESPONSE_CODE_APPROVED){
                if($result->getTransstate()>1000){
                    $payment->setStatus(self::STATUS_ERROR);
                    $payment->setStatusDescription(Mage::helper('paygate')->__('Voided transaction'));
                }elseif(in_array($result->getTransstate(),$this->_validVoidTransState)){
                     $payment->setStatus(self::STATUS_VOID);
                }
            }else{
                $payment->setStatus(self::STATUS_ERROR);
                $payment->setStatusDescription($result->getRespmsg()?
                    $result->getRespmsg():
                    Mage::helper('paygate')->__('Error in retreiving the transaction'));
            }
        }else{
            $payment->setStatus(self::STATUS_ERROR);
            $payment->setStatusDescription(Mage::helper('paygate')->__('Invalid transaction id'));
        }

        return $this;
    }

     /**
      * void
      *
      * @access public
      * @param string $payment Varien_Object object
      * @return Mage_Payment_Model_Abstract
      */
    public function void(Varien_Object $payment)
    {
         if($payment->getCcTransId()){
            $payment->setTrxtype(self::TRXTYPE_DELAYED_VOID);

            $request=$this->buildBasicRequest($payment);

            $result = $this->postRequest($request);

            if ($this->getConfigData('debug')) {
              $payment->setCcDebugRequestBody($result->getRequestBody())
                ->setCcDebugResponseSerialized(serialize($result));
            }
            if($result->getResultCode()==self::RESPONSE_CODE_APPROVED){
                 $payment->setStatus(self::STATUS_SUCCESS);
                 $payment->setCcTransId($result->getPnref());
            }else{
                $payment->setStatus(self::STATUS_ERROR);
                $payment->setStatusDescription($result->getRespmsg());
            }

         }else{
            $payment->setStatus(self::STATUS_ERROR);
            $payment->setStatusDescription(Mage::helper('paygate')->__('Invalid transaction id'));
        }

        return $this;

    }


     /**
      * refund the amount with transaction id
      *
      * @access public
      * @param string $payment Varien_Object object
      * @return Mage_Payment_Model_Abstract
      */
    public function refund(Varien_Object $payment, $amount)
    {
        if(($payment->getCcTransId() && $payment->getAmount()>0)){
            $payment->setTrxtype(self::TRXTYPE_CREDIT);

            $request=$this->buildBasicRequest($payment);

            $request->setAmt(round($payment->getAmount(),2));

            $result = $this->postRequest($request);

            if ($this->getConfigData('debug')) {
              $payment->setCcDebugRequestBody($result->getRequestBody())
                ->setCcDebugResponseSerialized(serialize($result));
            }
            if($result->getResultCode()==self::RESPONSE_CODE_APPROVED){
                 $payment->setStatus(self::STATUS_SUCCESS);
                 $payment->setCcTransId($result->getPnref());
            }else{
                $payment->setStatus(self::STATUS_ERROR);
                $payment->setStatusDescription($result->getRespmsg()?
                    $result->getRespmsg():
                    Mage::helper('paygate')->__('Error in refunding the payment.'));
            }
        }else{
            $payment->setStatus(self::STATUS_ERROR);
            $payment->setStatusDescription(Mage::helper('paygate')->__('Error in refunding the payment'));
        }

        return $this;

    }
}