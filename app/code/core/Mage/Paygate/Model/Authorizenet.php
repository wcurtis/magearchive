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


class Mage_Paygate_Model_Authorizenet extends Mage_Payment_Model_Method_Cc
{
    const CGI_URL = 'https://secure.authorize.net/gateway/transact.dll';

    const REQUEST_METHOD_CC = 'CC';
    const REQUEST_METHOD_ECHECK = 'ECHECK';

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT = 'CREDIT';
    const REQUEST_TYPE_VOID = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    const ECHECK_ACCT_TYPE_CHECKING = 'CHECKING';
    const ECHECK_ACCT_TYPE_BUSINESS = 'BUSINESSCHECKING';
    const ECHECK_ACCT_TYPE_SAVINGS = 'SAVINGS';

    const ECHECK_TRANS_TYPE_CCD = 'CCD';
    const ECHECK_TRANS_TYPE_PPD = 'PPD';
    const ECHECK_TRANS_TYPE_TEL = 'TEL';
    const ECHECK_TRANS_TYPE_WEB = 'WEB';

    const RESPONSE_DELIM_CHAR = ',';

    const RESPONSE_CODE_APPROVED = 1;
    const RESPONSE_CODE_DECLINED = 2;
    const RESPONSE_CODE_ERROR = 3;
    const RESPONSE_CODE_HELD = 4;

    protected $_code  = 'authorizenet';

    public function createFormBlock($name)
    {
        $hidden = array(
            'anet_trans_method'=>self::REQUEST_METHOD_CC,
        );

        $block = $this->getLayout()->createBlock('payment/form_cc', $name)
            ->setMethod('authorizenet')
            ->setPayment($this->getPayment())
            ->setHidden($hidden);
        return $block;
    }

    public function createInfoBlock($name)
    {
        $block = $this->getLayout()->createBlock('payment/info_cc', $name)
            ->setPayment($this->getPayment());
        return $block;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
        // $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY);
        $payment->setAnetTransType(Mage::getStoreConfig('payment/authorizenet/payment_action'));
        $payment->setDocument($payment->getOrder());

        $request = $this->buildRequest($payment);
        $result = $this->postRequest($request);

        $payment->setCcApproval($result->getApprovalCode())
            ->setCcTransId($result->getTransactionId())
            ->setCcAvsStatus($result->getAvsResultCode())
            ->setCcCidStatus($result->getCardCodeResponseCode());

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                $payment->setStatus('APPROVED');
                #$payment->getOrder()->addStatusToHistory(Mage::getStoreConfig('payment/authorizenet/order_status'));
                break;

            case self::RESPONSE_CODE_DECLINED:
                $payment->setStatus('DECLINED');
                $payment->setStatusDescription($result->getResponseReasonText());
                break;
            case self::RESPONSE_CODE_ERROR:
                $payment->setStatus('ERROR');
                $payment->setStatusDescription($result->getResponseReasonText());
                break;
            default:
                $payment->setStatus('UNKNOWN');
                $payment->setStatusDescription($result->getResponseReasonText());
                break;
        }

        return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {
        $payment->setDocument($payment->getInvoice());

        foreach ($order->getAllPayments() as $transaction) {
            break;
        }
        if ($transaction->getAnetTransId()) {
            $transaction->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
        }
        if ($transaction->getAnetAuthCode()) {
            $transaction->setAnetTransType(self::REQUEST_TYPE_CAPTURE_ONLY);
        }

        $request = $this->buildRequest($transaction);
    }

    /**
     * Enter description here...
     *
     * @link http://www.authorize.net/support/AIM_guide.pdf
     * @param Mage_Sales_Model_Document $document
     * @return unknown
     */
    public function buildRequest(Varien_Object $payment)
    {
        $document = $payment->getDocument();

        if (!$payment->getAnetTransMethod()) {
            $payment->setAnetTransMethod(self::REQUEST_METHOD_CC);
        }

        $request = Mage::getModel('paygate/authorizenet_request')
            ->setXVersion(3.1)
            ->setXDelimData('True')
            ->setXDelimChar(self::RESPONSE_DELIM_CHAR)
            ->setXRelayResponse('False');

        $request->setXTestRequest(Mage::getStoreConfig('payment/authorizenet/test') ? 'TRUE' : 'FALSE');

        $request->setXLogin(Mage::getStoreConfig('payment/authorizenet/login'))
            ->setXTranKey(Mage::getStoreConfig('payment/authorizenet/trans_key'))
            ->setXAmount($payment->getAmount())
            ->setXType($payment->getAnetTransType())
            ->setXMethod($payment->getAnetTransMethod());

        switch ($payment->getAnetTransType()) {
            case self::REQUEST_TYPE_CREDIT:
            case self::REQUEST_TYPE_VOID:
            case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                $request->setXTransId($payment->getCcTransId());
                break;

            case self::REQUEST_TYPE_CAPTURE_ONLY:
                $request->setXAuthCode($payment->getCcAuthCode());
                break;
        }

        if (!empty($document)) {
            $request->setXInvoiceNum($document->getIncrementId());

            $billing = $document->getBillingAddress();
            if (!empty($billing)) {
                $request->setXFirstName($billing->getFirstname())
                    ->setXLastName($billing->getLastname())
                    ->setXCompany($billing->getCompany())
                    ->setXAddress($billing->getStreet(1))
                    ->setXCity($billing->getCity())
                    ->setXState($billing->getRegion())
                    ->setXZip($billing->getPostcode())
                    ->setXCountry($billing->getCountry())
                    ->setXPhone($billing->getTelephone())
                    ->setXFax($billing->getFax())
                    ->setXCustId($billing->getCustomerId())
                    ->setXCustomerIp($document->getRemoteIp())
                    ->setXCustomerTaxId($billing->getTaxId())
                    ->setXEmail($billing->getEmail())
                    ->setXEmailCustomer(Mage::getStoreConfig('paygate/authorizenet/email_customer'))
                    ->setXMerchantEmail(Mage::getStoreConfig('paygate/authorizenet/merchant_email'));
            }

            $shipping = $document->getShippingAddress();
            if (!empty($shipping)) {
                $request->setXShipToFirstName($shipping->getFirstname())
                    ->setXShipToLastName($shipping->getLastname())
                    ->setXShipToCompany($shipping->getCompany())
                    ->setXShipToAddress($shipping->getStreet(1))
                    ->setXShipToCity($shipping->getCity())
                    ->setXShipToState($shipping->getRegion())
                    ->setXShipToZip($shipping->getPostcode())
                    ->setXShipToCountry($shipping->getCountry());
            }

            /** TODO: itemized order information
            $items = $document->getEntitiesByType('item');
            foreach ($items as $item) {

            }
            */

            $request->setXPoNum($payment->getPoNumber())
                ->setXTax($shipping->getTaxAmount())
                ->setXFreight($shipping->getShippingAmount());
        }

        switch ($payment->getAnetTransMethod()) {
            case self::REQUEST_METHOD_CC:
                $request->setXCardNum($payment->getCcNumber())
                    ->setXExpDate(sprintf('%02d-%04d', $payment->getCcExpMonth(), $payment->getCcExpYear()))
                    ->setXCardCode($payment->getCcCid());
                break;

            case self::REQUEST_METHOD_ECHECK:
                $request->setXBankAbaCode($payment->getEcheckRoutingNumber())
                    ->setXBankName($payment->getEcheckBankName())
                    ->setXBankAcctNum($payment->getEcheckAccountNumber())
                    ->setXBankAcctType($payment->getEcheckAccountType())
                    ->setXBankAcctName($payment->getEcheckAccountName())
                    ->setXEcheckType($payment->getEcheckType());
                break;
        }

        return $request;
    }

    public function postRequest(Varien_Object $request)
    {
        $result = Mage::getModel('paygate/authorizenet_result');

        $client = new Varien_Http_Client();
        $uri = self::CGI_URL;
        $client->setUri($uri);
        $client->setConfig(array(
            'maxredirects'=>0,
            'timeout'=>30,
            //'ssltransport' => 'tcp',
        ));
        $client->setParameterPost($request->getData());
        $client->setMethod(Zend_Http_Client::POST);

        if (Mage::getStoreConfig('payment/authorizenet/debug')) {
            foreach( $request->getData() as $key => $value ) {
                $requestData[] = strtoupper($key) . '=' . $value;
            }

            $requestData = join('&', $requestData);

            $debug = Mage::getModel('paygate/authorizenet_debug')
                ->setRequestBody($requestData)
                ->setRequestSerialized(serialize($request->getData()))
                ->setRequestDump(print_r($request->getData(),1))
                ->save();
        }

        try {
            $response = $client->request();
        } catch (Exception $e) {
            $result->setResponseCode(-1)
                ->setResponseReasonCode($e->getCode())
                ->setResponseReasonText($e->getMessage());

            if (!empty($debug)) {
                $debug
                    ->setResponseBody($response->getBody())
                    ->setResultSerialized(serialize($result->getData()))
                    ->setResultDump(print_r($result->getData(),1))
                    ->save();
            }

            return $result;
        }

        $requestArr = array();
        foreach ($request->getData() as $key=>$value) {
            $requestArr[] = urlencode($key).'='.urlencode($value);
        }
        $requestBody = join('&', $requestArr);
        $responseBody = $response->getBody();

        $r = explode(self::RESPONSE_DELIM_CHAR, $responseBody);

        $result->setResponseCode($r[0])
            ->setResponseSubcode($r[1])
            ->setResponseReasonCode($r[2])
            ->setResponseReasonText($r[3])
            ->setApprovalCode($r[4])
            ->setAvsResultCode($r[5])
            ->setTransactionId($r[6])
            ->setInvoiceNumber($r[7])
            ->setDescription($r[8])
            ->setAmount($r[9])
            ->setMethod($r[10])
            ->setTransactionType($r[11])
            ->setCustomerId($r[12])
            ->setMd5Hash($r[37])
            ->setCardCodeResponseCode($r[39]);

        if (!empty($debug)) {
            $debug
                ->setResponseBody($responseBody)
                ->setResultSerialized(serialize($result->getData()))
                ->setResultDump(print_r($result->getData(),1))
                ->save();
        }

        return $result;
    }


      /**
      * canVoid
      *
      * @access public
      * @param string $payment Mage_Payment_Model_Info object
      * @return Mage_Payment_Model_Abstract
      * @desc authorizenet does not have inquiry type for transaction, so just return void
      *         to start with void
      */
    public function canVoid(Mage_Payment_Model_Info $payment)
    {
        $payment->setStatus('VOID');
        return $this;

    }

      /**
      * void
      *
      * @access public
      * @param string $payment Mage_Payment_Model_Info object
      * @return Mage_Payment_Model_Abstract
      */
    public function void(Mage_Payment_Model_Info $payment)
    {
        if($payment->getCcTransId()){
            $payment->setAnetTransType(self::REQUEST_TYPE_VOID);
            $request = $this->buildRequest($payment);
            $result = $this->postRequest($request);
            if($result->getResponseCode()==self::RESPONSE_CODE_APPROVED){
                 $payment->setStatus('SUCCESS');
            }else{
                $payment->setStatus('ERROR');
                $payment->setStatusDescription($result->getResponseReasonText());
            }
        }else{
            $payment->setStatus('ERROR');
            $payment->setStatusDescription(Mage::helper('paygate')->__('Invalid transaction id'));
        }
        return $this;
    }

    /**
     * Check refund availability
     * @desc overiding the parent abstract
     * @return bool
     */
    public function canRefund()
    {
        return true;
    }

     /**
      * refund the amount with transaction id
      *
      * @access public
      * @param string $payment Mage_Payment_Model_Info object
      * @return Mage_Payment_Model_Abstract
      */
     public function refund(Mage_Payment_Model_Info $payment)
     {
         if($payment->getCcTransId() && $payment->getAmount()>0){
            $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
            $request = $this->buildRequest($payment);
            $result = $this->postRequest($request);
            if($result->getResponseCode()==self::RESPONSE_CODE_APPROVED){
                 $payment->setStatus('SUCCESS');
            }else{
                $payment->setStatus('ERROR');
                $payment->setStatusDescription($result->getResponseReasonText());
            }

         }else{
            $payment->setStatus('ERROR');
            $payment->setStatusDescription(Mage::helper('paygate')->__('Error in refunding the payment'));
         }

     }

}
