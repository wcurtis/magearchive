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
 * @package    Mage_SalesRule
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_SalesRule_Model_Rule_Condition_Product_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('salesrule/rule_condition_product_combine');
    }

    public function loadOperatorOptions()
    {
    	$this->setOperatorOption(array(
    		1=>'FOUND',
    		0=>'NOT FOUND',
    	));
    	return $this;
    }

    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'salesrule/rule_condition_product', 'label'=>'Product attribute'),
        ));
        return $conditions;
    }

    public function asHtml()
    {
    	$html = $this->getTypeElement()->getHtml().
    	    Mage::helper('salesrule')->__("If an item is %s in the cart with %s of these conditions true:",
    		$this->getOperatorElement()->getHtml(), $this->getAttributeElement()->getHtml());
       	if ($this->getId()!='1') {
       	    $html.= $this->getRemoveLinkHtml();
       	}
    	return $html;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        $all = $this->getAttribute()==='all';
        $found = false;
        foreach ($object->getAllItems() as $item) {
            $found = $all ? true : false;
            foreach ($this->getConditions() as $cond) {
                if ($all && !$cond->validate($item)) {
                    $found = false;
                    break;
                } elseif (!$all && $cond->validate($item)) {
                    $found = true;
                    break 2;
                }
            }
            if ($found && (bool)$this->getOperator()) {
                break;
            }
        }
        if ($found && (bool)$this->getOperator()) {
            // found an item and we're looking for existing one

            return true;
        } elseif (!$found && !(bool)$this->getOperator()) {
            // not found and we're making sure it doesn't exist
            return true;
        }
        return false;
    }
}
