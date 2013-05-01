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
 * @package    Mage_Rule
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Abstract class for quote rule condition
 *
 */
abstract class Mage_Rule_Model_Condition_Abstract
	extends Varien_Object
	implements Mage_Rule_Model_Condition_Interface
{
    public function __construct()
    {
        parent::__construct();

        $this->loadAttributeOptions()->loadOperatorOptions()->loadValueOptions();

        foreach ($this->getAttributeOption() as $attr=>$dummy) { $this->setAttribute($attr); break; }
        foreach ($this->getOperatorOption() as $operator=>$dummy) { $this->setOperator($operator); break; }
    }

    public function getForm()
    {
        return $this->getRule()->getForm();
    }

    public function asArray(array $arrAttributes = array())
    {
        $out = array(
            'type'=>$this->getType(),
            'attribute'=>$this->getAttribute(),
            'operator'=>$this->getOperator(),
            'value'=>$this->getValue(),
        );
        return $out;
    }

    public function asXml()
    {
        extract($this->toArray());
        $xml = "<type>".$this->getType()."</type>"
            ."<attribute>".$this->getAttribute()."</attribute>"
            ."<operator>".$this->getOperator()."</operator>"
            ."<value>".$this->getValue()."</value>";
        return $xml;
    }

    public function loadArray($arr)
    {
        $this->addData(array(
            'type'=>$arr['type'],
            'attribute'=>isset($arr['attribute']) ? $arr['attribute'] : false,
            'operator'=>isset($arr['operator']) ? $arr['operator'] : false,
            'value'=>isset($arr['value']) ? $arr['value'] : false,
        ));
        $this->loadAttributeOptions();
        $this->loadOperatorOptions();
        $this->loadValueOptions();
        return $this;
    }

    public function loadXml($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $arr = (array)$xml;
        $this->loadArray($arr);
        return $this;
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array());
        return $this;
    }

    public function getAttributeSelectOptions()
    {
    	$opt = array();
    	foreach ($this->getAttributeOption() as $k=>$v) {
    		$opt[] = array('value'=>$k, 'label'=>$v);
    	}
    	return $opt;
    }

    public function getAttributeName()
    {
        return $this->getAttributeOption($this->getAttribute());
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rule')->__('is'),
            '!='  => Mage::helper('rule')->__('is not'),
            '>='  => Mage::helper('rule')->__('equals or greater than'),
            '<='  => Mage::helper('rule')->__('equals or less than'),
            '>'   => Mage::helper('rule')->__('greater than'),
            '<'   => Mage::helper('rule')->__('less than'),
            '{}'  => Mage::helper('rule')->__('contains'),
            '!{}' => Mage::helper('rule')->__('does not contain'),
            '()'  => Mage::helper('rule')->__('is one of'),
            '!()' => Mage::helper('rule')->__('is not one of'),
        ));
        return $this;
    }

    public function getOperatorSelectOptions()
    {
    	$opt = array();
    	foreach ($this->getOperatorOption() as $k=>$v) {
    		$opt[] = array('value'=>$k, 'label'=>$v);
    	}
    	return $opt;
    }

    public function getOperatorName()
    {
        return $this->getOperatorOption($this->getOperator());
    }

    public function loadValueOptions()
    {
        $this->setValueOption(array(
            true  => Mage::helper('rule')->__('TRUE'),
            false => Mage::helper('rule')->__('FALSE'),
        ));
        return $this;
    }

    public function getValueSelectOptions()
    {
    	$opt = array();
    	foreach ($this->getValueOption() as $k=>$v) {
    		$opt[] = array('value'=>$k, 'label'=>$v);
    	}
    	return $opt;
    }

    public function getValueName()
    {
        $value = $this->getValue();
        if (is_null($value)) {
            return '...';
        }
        if (is_string($value)) {
            return $value!=='' ? $value : '...';
        }
        if (is_bool($value)) {
            return $this->getValueOption($value);
        }
        return $value;
    }

    public function getNewChildSelectOptions()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('rule')->__('Please choose a condition to add...')),
        );
    }

    public function getNewChildName()
    {
        return $this->getAddLinkHtml();
    }

    public function asHtml()
    {
    	$html = $this->getTypeElement()->getHtml()
    	   .$this->getAttributeElement()->getHtml().' '
    	   .$this->getOperatorElement()->getHtml().' '
    	   .$this->getValueElement()->getHtml()
    	   .$this->getRemoveLinkHtml();
    	return $html;
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml();
        return $html;
    }

    public function getTypeElement()
    {
    	return $this->getForm()->addField('cond:'.$this->getId().':type', 'hidden', array(
    		'name'=>'rule[conditions]['.$this->getId().'][type]',
    		'value'=>$this->getType(),
    		'no_span'=>true,
    	));
    }

    public function getAttributeElement()
    {
    	return $this->getForm()->addField('cond:'.$this->getId().':attribute', 'select', array(
    		'name'=>'rule[conditions]['.$this->getId().'][attribute]',
    		'values'=>$this->getAttributeSelectOptions(),
    		'value'=>$this->getAttribute(),
    		'value_name'=>$this->getAttributeName(),
    	))->setRenderer(Mage::getHelper('rule/editable'));
    }

    public function getOperatorElement()
    {
        return $this->getForm()->addField('cond:'.$this->getId().':operator', 'select', array(
    		'name'=>'rule[conditions]['.$this->getId().'][operator]',
    		'values'=>$this->getOperatorSelectOptions(),
    		'value'=>$this->getOperator(),
    		'value_name'=>$this->getOperatorName(),
    	))->setRenderer(Mage::getHelper('rule/editable'));
    }

    public function getValueElement()
    {
        return $this->getForm()->addField('cond:'.$this->getId().':value', 'text', array(
    		'name'=>'rule[conditions]['.$this->getId().'][value]',
    		'value'=>$this->getValue(),
    		'value_name'=>$this->getValueName(),
    	))->setRenderer(Mage::getHelper('rule/editable'));
    }

    public function getAddLinkHtml()
    {
    	$src = Mage::getDesign()->getSkinUrl('images/rule_component_add.gif');
    	$html = '<img src="'.$src.'" class="rule-param-add v-middle"/>';
        return $html;
    }


    public function getRemoveLinkHtml()
    {
    	$src = Mage::getDesign()->getSkinUrl('images/rule_component_remove.gif');
        $html = ' <span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove"><img src="'.$src.'" class="v-middle"/></a></span>';
        return $html;
    }

    public function asString($format='')
    {
        $str = $this->getAttributeName().' '.$this->getOperatorName().' '.$this->getValueName();
        return $str;
    }

    public function asStringRecursive($level=0)
    {
        $str = str_pad('', $level*3, ' ', STR_PAD_LEFT).$this->asString();
        return $str;
    }

    public function validateAttribute($validatedValue)
    {
        // $validatedValue suppose to be simple alphanumeric value
        if (is_array($validatedValue) || is_object($validatedValue)) {
            return false;
        }

        $op = $this->getOperator();

        // if operator requires array and it is not, or on opposite, return false
        if ((($op=='()' || $op=='!()') && !is_array($this->getValue()))
            || (!($op=='()' || $op=='!()') && is_array($this->getValue()))) {
            return false;
        }

        $result = false;

        switch ($op) {
            case '==': case '!=':
                $result = $validatedValue==$this->getValue();
                break;

            case '<=': case '>':
                $result = $validatedValue<=$this->getValue();
                break;

            case '>=': case '<':
                $result = $validatedValue>=$this->getValue();
                break;

            case '{}': case '!{}':
                $result = stripos((string)$validatedValue, (string)$this->getValue())!==false;
                break;

            case '()': case '!()':
                $result = in_array($validatedValue, (array)$this->getValue());
                break;
        }

        if ('!='==$op || '>'==$op || '<'==$op || '!{}'==$op || '!()'==$op) {
            $result = !$result;
        }

        return $result;
    }

    public function validate(Varien_Object $object)
    {
        return $this->validateAttribute($object->getData($this->getAttribute()));
    }
}