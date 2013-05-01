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
 * @package    Mage_Usa
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * UPS shipping rates estimation
 *
 * @category   Mage
 * @package    Mage_Usa
 */
class Mage_Usa_Model_Shipping_Carrier_Ups extends Mage_Usa_Model_Shipping_Carrier_Abstract
{
    protected $_request = null;
    protected $_result = null;
    protected $_xmlAccessRequest = null;
    protected $_defaultCgiGatewayUrl = 'http://www.ups.com:80/using/services/rave/qcostcgi.cgi';
    public $isTrackingAvailable = true;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::getStoreConfig('carriers/ups/active')) {
            return false;
        }

        $this->setRequest($request);
        if (!$request->getUpsRequestMethod()) {
            if(Mage::getStoreConfig('carriers/ups/type')=='UPS')
        		$request->setUpsRequestMethod('cgi');
        	elseif (Mage::getStoreConfig('carriers/ups/type')=='UPS_XML')
        		$request->setUpsRequestMethod('xml');
        }

        switch ($request->getUpsRequestMethod()) {
            case 'cgi':
                $this->_getCgiQuotes();
                break;

            case 'xml':
                $this->_getXmlQuotes();
                break;
        }
        return $this->getResult();
    }

    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setAction($this->getCode('action', 'single'));
            $r->setProduct($request->getLimitMethod());
        } else {
            $r->setAction($this->getCode('action', 'all'));
            $r->setProduct('GNDRES');
        }

        if ($request->getUpsPickup()) {
            $pickup = $request->getUpsPickup();
        } else {
            $pickup = Mage::getStoreConfig('carriers/ups/pickup');
        }
        $r->setPickup($this->getCode('pickup', $pickup));

        if ($request->getUpsContainer()) {
            $container = $request->getUpsContainer();
        } else {
            $container = Mage::getStoreConfig('carriers/ups/container');
        }
        $r->setContainer($this->getCode('container', $container));

        if ($request->getUpsDestType()) {
            $destType = $request->getUpsDestType();
        } else {
            $destType = Mage::getStoreConfig('carriers/ups/dest_type');
        }
        $r->setDestType($this->getCode('dest_type', $destType));

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = Mage::getStoreConfig('shipping/origin/country_id');
        }

        $r->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code());

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode'));
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        } else {

        }

        $r->setWeight($request->getPackageWeight());

        $r->setValue($request->getPackageValue());

        if ($request->getUpsUnitMeasure()) {
            $unit = $request->getUpsUnitMeasure();
        } else {
            $unit = Mage::getStoreConfig('carriers/ups/unit_of_measure');
        }
        $r->setUnitMeasure($unit);

        $this->_rawRequest = $r;

        return $this;
    }

    public function getResult()
    {
       return $this->_result;
    }

    protected function _getCgiQuotes()
    {
        $r = $this->_rawRequest;

        $params = array(
            'accept_UPS_license_agreement' => 'yes',
            '10_action'      => $r->getAction(),
            '13_product'     => $r->getProduct(),
            '14_origCountry' => $r->getOrigCountry(),
            '15_origPostal'  => $r->getOrigPostal(),
            '19_destPostal'  => $r->getDestPostal(),
            '22_destCountry' => $r->getDestCountry(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
        );
        $params['47_rate_chart'] = $params['47_rate_chart']['label'];
        try {
            $url = Mage::getStoreConfig('carriers/ups/gateway_url');
            if (!$url) {
                $url = $this->_defaultCgiGatewayUrl;
            }
            $client = new Zend_Http_Client();
            $client->setUri($url);
            $client->setConfig(array('maxredirects'=>0, 'timeout'=>30));
            $client->setParameterGet($params);
            $response = $client->request();
            $responseBody = $response->getBody();
        } catch (Exception $e) {
            $responseBody = '';
        }

        $this->_parseCgiResponse($responseBody);
    }

	public function getShipmentByCode($code,$origin = null){
		if($origin===null){
			$origin = Mage::getStoreConfig('carriers/ups/origin_shipment');
		}
		$arr = $this->getCode('originShipment',$origin);
		if(isset($arr[$code]))
			return $arr[$code];
		else
			return false;
	}

    protected function _parseXmlResponse($xmlResponse)
    {
    	$xml = new Varien_Simplexml_Config();
		$xml->loadString($xmlResponse);
		$arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/ResponseStatusCode/text()");
		$success = (int)$arr[0][0];
		$result = Mage::getModel('shipping/rate_result');

		if($success===1){
			$arr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment");
			$allowedMethods = explode(",", Mage::getStoreConfig('carriers/ups/allowed_methods'));
			$costArr = array();
	        $priceArr = array();
			foreach ($arr as $shipElement){
				$code = (string)$shipElement->Service->Code;
				#$shipment = $this->getShipmentByCode($code);
				if (in_array($code, $allowedMethods)) {
                    $costArr[$code] = $shipElement->TotalCharges->MonetaryValue;
                    $priceArr[$code] = $this->getMethodPrice(floatval($shipElement->TotalCharges->MonetaryValue),$code);
                }
			}
		} else {
			$arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/Error/ErrorDescription/text()");
			$errorTitle = (string)$arr[0][0];
			$error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle(Mage::getStoreConfig('carriers/ups/title'));
            $error->setErrorMessage($errorTitle);

		}


        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle(Mage::getStoreConfig('carriers/ups/title'));
            if(!isset($errorTitle)){
            	$errorTitle = Mage::helper('usa')->__('Sorry not Found');
			}
            $error->setErrorMessage($errorTitle);
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('ups');
                $rate->setCarrierTitle(Mage::getStoreConfig('carriers/ups/title'));
                $rate->setMethod($method);
                $method_arr = $this->getShipmentByCode($method);
                $rate->setMethodTitle(Mage::helper('usa')->__($method_arr));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
        $this->_result = $result;
    }

    protected function _parseCgiResponse($response)
    {
        $rRows = explode("\n", $response);
        $costArr = array();
        $priceArr = array();
        $errorTitle = Mage::helper('usa')->__('Unknown error');
        $allowedMethods = explode(",", Mage::getStoreConfig('carriers/ups/allowed_methods'));
        foreach ($rRows as $rRow) {
            $r = explode('%', $rRow);
            switch (substr($r[0],-1)) {
                case 3: case 4:
                    if (in_array($r[1], $allowedMethods)) {
                        $costArr[$r[1]] = $r[8];
                        $priceArr[$r[1]] = $this->getMethodPrice($r[8], $r[1]);
                    }
                    break;
                case 5:
                    $errorTitle = $r[1];
                    break;
                case 6:
                    if (in_array($r[3], $allowedMethods)) {
                        $costArr[$r[3]] = $r[10];
                        $priceArr[$r[3]] = $this->getMethodPrice($r[10], $r[3]);
                    }
                    break;
            }
        }
        asort($priceArr);

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle(Mage::getStoreConfig('carriers/ups/title'));
            $error->setErrorMessage($errorTitle);
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('ups');
                $rate->setCarrierTitle(Mage::getStoreConfig('carriers/ups/title'));
                $rate->setMethod($method);
                $method_arr = $this->getCode('method', $method);
                $rate->setMethodTitle(Mage::helper('usa')->__($method_arr));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
#echo "<pre>".print_r($result,1)."</pre>";
        $this->_result = $result;
    }

    public function getMethodPrice($cost, $method='')
    {
    	$r = $this->_rawRequest;
        if (Mage::getStoreConfig('carriers/ups/cutoff_cost') != ''
         && $method == Mage::getStoreConfig('carriers/ups/free_method')
         && Mage::getStoreConfig('carriers/ups/cutoff_cost') <= $r->getValue()) {
             $price = '0.00';
        } else {
            $price = $cost + Mage::getStoreConfig('carriers/ups/handling');
        }
        return $price;
    }

/*
    public function isEligibleForFree($method)
    {
        return $method=='GND' || $method=='GNDCOM' || $method=='GNDRES';
    }
*/

    public function getCode($type, $code='')
    {
        $codes = array(
            'action'=>array(
                'single'=>'3',
                'all'=>'4',
            ),

            'originShipment'=>array(
            	// United States Domestic Shipments
	            'United States Domestic Shipments' => array(
	                '01' => 'UPS Next Day Air',
	                '02' => 'UPS Second Day Air',
	                '03' => 'UPS Ground',
	                '07' => 'UPS Worldwide Express',
	                '08' => 'UPS Worldwide Expedited',
	                '11' => 'UPS Standard',
	                '12' => 'UPS Three-Day Select',
	                '13' => 'UPS Next Day Air Saver',
	                '14' => 'UPS Next Day Air Early A.M.',
	                '54' => 'UPS Worldwide Express Plus',
	                '59' => 'UPS Second Day Air A.M.',
	                '65' => 'UPS Saver',
	            ),
	            // Shipments Originating in United States
	            'Shipments Originating in United States' => array(
	                '01' => 'UPS Next Day Air',
	                '02' => 'UPS Second Day Air',
	                '03' => 'UPS Ground',
	                '07' => 'UPS Worldwide Express',
	                '08' => 'UPS Worldwide Expedited',
	                '11' => 'UPS Standard',
	                '12' => 'UPS Three-Day Select',
	                '14' => 'UPS Next Day Air Early A.M.',
	                '54' => 'UPS Worldwide Express Plus',
	                '59' => 'UPS Second Day Air A.M.',
	                '65' => 'UPS Saver',
	            ),
	            // Shipments Originating in Canada
	            'Shipments Originating in Canada' => array(
	                '01' => 'UPS Express',
	                '02' => 'UPS Expedited',
	                '07' => 'UPS Worldwide Express',
	                '08' => 'UPS Worldwide Expedited',
	                '11' => 'UPS Standard',
	                '12' => 'UPS Three-Day Select',
	                '14' => 'UPS Express Early A.M.',
	                '65' => 'UPS Saver',
	            ),
	            // Shipments Originating in the European Union
	            'Shipments Originating in the European Union' => array(
	                '07' => 'UPS Express',
	                '08' => 'UPS Expedited',
	                '11' => 'UPS Standard',
	                '54' => 'UPS Worldwide Express PlusSM',
	                '65' => 'UPS Saver',
	            ),
	            // Polish Domestic Shipments
	            'Polish Domestic Shipments' => array(
	                '07' => 'UPS Express',
	                '08' => 'UPS Expedited',
	                '11' => 'UPS Standard',
	                '54' => 'UPS Worldwide Express Plus',
	                '65' => 'UPS Saver',
	                '82' => 'UPS Today Standard',
	                '83' => 'UPS Today Dedicated Courrier',
	                '84' => 'UPS Today Intercity',
	                '85' => 'UPS Today Express',
	                '86' => 'UPS Today Express Saver',
	            ),
	            // Puerto Rico Origin
	            'Puerto Rico Origin' => array(
	                '01' => 'UPS Next Day Air',
	                '02' => 'UPS Second Day Air',
	                '03' => 'UPS Ground',
	                '07' => 'UPS Worldwide Express',
	                '08' => 'UPS Worldwide Expedited',
	                '14' => 'UPS Next Day Air Early A.M.',
	                '54' => 'UPS Worldwide Express Plus',
	                '65' => 'UPS Saver',
	            ),
	            // Shipments Originating in Mexico
	            'Shipments Originating in Mexico' => array(
	                '07' => 'UPS Express',
	                '08' => 'UPS Expedited',
	                '54' => 'UPS Express Plus',
	                '65' => 'UPS Saver',
	            ),
	            // Shipments Originating in Other Countries
	            'Shipments Originating in Other Countries' => array(
	                '07' => 'UPS Express',
	                '08' => 'UPS Worldwide Expedited',
	                '11' => 'UPS Standard',
	                '54' => 'UPS Worldwide Express Plus',
	                '65' => 'UPS Saver'
	            )
            ),

            'method'=>array(
                '1DM'    => 'Next Day Air Early AM',
                '1DML'   => 'Next Day Air Early AM Letter',
                '1DA'    => 'Next Day Air',
                '1DAL'   => 'Next Day Air Letter',
                '1DAPI'  => 'Next Day Air Intra (Puerto Rico)',
                '1DP'    => 'Next Day Air Saver',
                '1DPL'   => 'Next Day Air Saver Letter',
                '2DM'    => '2nd Day Air AM',
                '2DML'   => '2nd Day Air AM Letter',
                '2DA'    => '2nd Day Air',
                '2DAL'   => '2nd Day Air Letter',
                '3DS'    => '3 Day Select',
                'GND'    => 'Ground',
                'GNDCOM' => 'Ground Commercial',
                'GNDRES' => 'Ground Residential',
                'STD'    => 'Canada Standard',
                'XPR'    => 'Worldwide Express',
                'WXS'    => 'Worldwide Express Saver',
                'XPRL'   => 'Worldwide Express Letter',
                'XDM'    => 'Worldwide Express Plus',
                'XDML'   => 'Worldwide Express Plus Letter',
                'XPD'    => 'Worldwide Expedited',
            ),

            'pickup'=>array(
                'RDP'    => array("label"=>'Regular Daily Pickup',"code"=>"01"),
                'OCA'    => array("label"=>'On Call Air',"code"=>"07"),
                'OTP'    => array("label"=>'One Time Pickup',"code"=>"06"),
                'LC'     => array("label"=>'Letter Center',"code"=>"19"),
                'CC'     => array("label"=>'Customer Counter',"code"=>"03"),
            ),

            'container'=>array(
                'CP'     => '00', // Customer Packaging
                'ULE'    => '01', // UPS Letter Envelope
                'UT'     => '03', // UPS Tube
                'UEB'    => '21', // UPS Express Box
                'UW25'   => '24', // UPS Worldwide 25 kilo
                'UW10'   => '25', // UPS Worldwide 10 kilo
            ),

            'container_description'=>array(
                'CP'     => 'Customer Packaging',
                'ULE'    => 'UPS Letter Envelope',
                'UT'     => 'UPS Tube',
                'UEB'    => 'UPS Express Box',
                'UW25'   => 'UPS Worldwide 25 kilo',
                'UW10'   => 'UPS Worldwide 10 kilo',
            ),

            'dest_type'=>array(
                'RES'    => '1', // Residential
                'COM'    => '2', // Commercial
            ),

            'dest_type_description'=>array(
                'RES'    => 'Residential',
                'COM'    => 'Commercial',
            ),

            'unit_of_measure'=>array(
                'LBS'   =>  'Pounds',
                'KGS'   =>  'Kilograms',
            ),

        );

        if (!isset($codes[$type])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid UPS CGI code type: %s', $type));
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid UPS CGI code for type %s: %s', $type, $code));
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    protected function _getXmlQuotes()
    {
		$url = Mage::getStoreConfig('carriers/ups/gateway_xml_url');

        $this->setXMLAccessRequest();
        $xmlRequest=$this->_xmlAccessRequest;

		$r = $this->_rawRequest;
		$params = array(
            'accept_UPS_license_agreement' => 'yes',
            '10_action'      => $r->getAction(),
            '13_product'     => $r->getProduct(),
            '14_origCountry' => $r->getOrigCountry(),
            '15_origPostal'  => $r->getOrigPostal(),
            '19_destPostal'  => $r->getDestPostal(),
            '22_destCountry' => $r->getDestCountry(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
        );
        $params['10_action']=='4'? $params['10_action']='Shop':$params['10_action']='Rate';
$xmlRequest .= <<< XMLRequest
<?xml version="1.0"?>
<RatingServiceSelectionRequest xml:lang="en-US">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>{$params['10_action']}</RequestOption>
  </Request>
  <Service>
  	<Code></Code>
  	<Description></Description>
  </Service>
  <PickupType>
  		<Code>{$params['47_rate_chart']['code']}</Code>
  		<Description>{$params['47_rate_chart']['label']}</Description>
  </PickupType>

  <Shipment>

  	<Shipper>
      <Address>
      	<PostalCode>{$params['15_origPostal']}</PostalCode>
      	<CountryCode>{$params['14_origCountry']}</CountryCode>
      </Address>
    </Shipper>

    <ShipTo>
      <Address>
      	<PostalCode>{$params['19_destPostal']}</PostalCode>
      	<CountryCode>{$params['22_destCountry']}</CountryCode>
      	<ResidentialAddress>{$params['49_residential']}</ResidentialAddress>
      </Address>
    </ShipTo>


    <ShipFrom>
      <Address>
      	<PostalCode>{$params['15_origPostal']}</PostalCode>
      	<CountryCode>{$params['14_origCountry']}</CountryCode>
      </Address>
    </ShipFrom>

    <Package>
      <PackagingType><Code>{$params['48_container']}</Code></PackagingType>
      <PackageWeight>
     	<UnitOfMeasurement><Code>{$r->getUnitMeasure()}</Code></UnitOfMeasurement>
        <Weight>{$params['23_weight']}</Weight>
      </PackageWeight>
    </Package>

  </Shipment>
</RatingServiceSelectionRequest>
XMLRequest;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$xmlResponse = curl_exec ($ch);
		$this->_parseXmlResponse($xmlResponse);
    }

    public function getTracking($trackings)
    {
        $return = array();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }

        if (Mage::getStoreConfig('carriers/ups/type')=='UPS') {
        	$this->_getCgiTracking($trackings);
        } elseif (Mage::getStoreConfig('carriers/ups/type')=='UPS_XML'){
            $this->setXMLAccessRequest();
            $this->_getXmlTracking($trackings);
        }

        return $this->_result;
    }

    protected function setXMLAccessRequest()
    {
        $userid = Mage::getStoreConfig('carriers/ups/username');
		$userid_pass = Mage::getStoreConfig('carriers/ups/password');
		$access_key = Mage::getStoreConfig('carriers/ups/access_license_number');

		$this->_xmlAccessRequest =  <<<XMLAuth
<?xml version="1.0"?>
<AccessRequest xml:lang="en-US">
  <AccessLicenseNumber>$access_key</AccessLicenseNumber>
  <UserId>$userid</UserId>
  <Password>$userid_pass</Password>
</AccessRequest>
XMLAuth;
    }

    protected function _getCgiTracking($trackings)
    {
        //ups no longer support tracking for data streaming version
        //so we can only reply the popup window to ups.
        $result = Mage::getModel('shipping/tracking_result');
        $defaults = $this->getDefaults();
        foreach($trackings as $tracking){
            $status = Mage::getModel('shipping/tracking_result_status');
            $status->setCarrier('ups');
            $status->setCarrierTitle(Mage::getStoreConfig('carriers/ups/title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("http://wwwapps.ups.com/WebTracking/processInputRequest?HTMLVersion=5.0&error_carried=true&tracknums_displayed=5&TypeOfInquiryNumber=T&loc=en_US&InquiryNumber1=$tracking&AgreeToTermsAndConditions=yes");
            $result->append($status);
        }

        $this->_result = $result;
        return $result;
    }

    protected function _getXmlTracking($trackings)
    {
        $url = Mage::getStoreConfig('carriers/ups/tracking_xml_url');

        foreach($trackings as $tracking){
            $xmlRequest=$this->_xmlAccessRequest;

$xmlRequest .=  <<<XMLAuth
<?xml version="1.0" ?>
<TrackRequest xml:lang="en-US">
    <Request>
        <RequestAction>Track</RequestAction>
        <RequestOption>1</RequestOption>
    </Request>
    <ShipmentIdentificationNumber>$tracking</ShipmentIdentificationNumber>
    <IncludeFreight>01</IncludeFreight>
</TrackRequest>
XMLAuth;

            try {
                $ch = curl_init();
               	curl_setopt($ch, CURLOPT_URL, $url);
            	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            	curl_setopt($ch, CURLOPT_HEADER, 0);
            	curl_setopt($ch, CURLOPT_POST, 1);
            	curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            	$xmlResponse = curl_exec ($ch);
            	curl_close ($ch);
            }catch (Exception $e) {
                $xmlResponse = '';
            }

            $this->_parseXmlTrackingResponse($tracking, $xmlResponse);
        }

        return $this->_result;
    }

    protected function _parseXmlTrackingResponse($trackingvalue, $xmlResponse)
    {
        $xml = new Varien_Simplexml_Config();
		$xml->loadString($xmlResponse);
		$arr = $xml->getXpath("//TrackResponse/Response/ResponseStatusCode/text()");
		$success = (int)$arr[0][0];
		$errorTitle = 'Unable to retrieve tracking';
		$resultArr=array();
        if($success===1){
            $arr=$xml->getXpath("//TrackResponse/Shipment/Package/Activity/Status/StatusType/Description/text()");
            $resultArr['status']=(string)$arr[0];
            $arr=$xml->getXpath("//TrackResponse/Shipment/Service/Description/text()");
            $resultArr['service']=(string)$arr[0];
            $arr=$xml->getXpath("//TrackResponse/Shipment/Package/Activity/Date/text()");
            $date=(string)$arr[0]; //YYYYMMDD
            $resultArr['deliverydate']=substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,-2,2);
            $arr=$xml->getXpath("//TrackResponse/Shipment/Package/Activity/Time/text()");
            $time=(string)$arr[0];//HHMM
            $resultArr['deliverytime']=substr($time,0,2).':'.substr($time,2,2).':'.'00';
            $arr=$xml->getXpath("//TrackResponse/Shipment/Package/Activity/ActivityLocation/Description/text()");
            $resultArr['deliverylocation']=(string)$arr[0];
            $arr=$xml->getXpath("//TrackResponse/Shipment/Package/Activity/ActivityLocation/SignedForByName/text()");
            $resultArr['signedby']=(string)$arr[0];
        }else{
            $arr = $xml->getXpath("//TrackResponse/Response/Error/ErrorDescription/text()");
            $errorTitle = (string)$arr[0][0];
        }

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }

        $defaults = $this->getDefaults();

        if($resultArr){
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier('ups');
            $tracking->setCarrierTitle(Mage::getStoreConfig('carriers/ups/title'));
            $tracking->setTracking($trackingvalue);
            $tracking->addData($resultArr);

            $this->_result->append($tracking);
        }else{
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle(Mage::getStoreConfig('carriers/ups/title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle);
            $this->_result->append($error);
        }

        return $this->_result;
    }

    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking){
                    if($data = $tracking->getAllData()){
                        if (isset($data['status'])) {
                            $statuses .= Mage::helper('usa')->__($data['status']);
                        } else {
                            $statuses .= Mage::helper('usa')->__($data['error_message']);
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = Mage::helper('usa')->__('Empty response');
        }
        return $statuses;
    }

    public function isTrackingAvailable()
    {
        return true;
    }
}
