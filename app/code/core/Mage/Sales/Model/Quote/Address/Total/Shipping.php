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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Quote_Address_Total_Shipping extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $oldWeight = $address->getWeight();

        $address->setWeight(0);

        foreach ($address->getAllItems() as $item) {
            $item->calcRowWeight();
            $address->setWeight($address->getWeight() + $item->getRowWeight());
        }

        $address->collectShippingRates();

        $address->setShippingAmount(0);
        $method = $address->getShippingMethod();
        if ($method) {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode()==$method) {
                    //$address->setShippingAmount($rate->getPrice());
                    $amountPrice = $address->getQuote()->getStore()->convertPrice($rate->getPrice(), false);
                    $address->setShippingAmount($amountPrice);
                    $address->setShippingDescription($rate->getCarrierTitle().' - '.$rate->getMethodDescription());
                    break;
                }
            }
        }

        $address->setGrandTotal($address->getGrandTotal() + $address->getShippingAmount());
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getShippingAmount();
        if ($amount!=0) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('sales')->__('Shipping & Handling').' ('.$address->getShippingDescription().')',
                'value'=>$address->getShippingAmount()
            ));
        }
        return $this;
    }
}