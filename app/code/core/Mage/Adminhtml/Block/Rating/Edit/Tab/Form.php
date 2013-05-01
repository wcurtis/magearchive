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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Poll edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Rating_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $defaultStore = Mage::getModel('core/store')->load(0);
        $fieldset = $form->addFieldset('rating_form', array('legend'=>Mage::helper('rating')->__('Rating Title')));
        $fieldset->addField('rating_code', 'text', array(
                                'label'     => $defaultStore->getName(),
                                'class'     => 'required-entry',
                                'required'  => true,
                                'name'      => 'rating_code',
                            )
        );

        $stores = Mage::app()->getStore()->getResourceCollection()->load()->toOptionArray();
        foreach($stores as $store) {
            $fieldset->addField('rating_code_' . $store['value'], 'text', array(
                                    'label'     =>  $store['label'],
                                    'name'      => 'rating_codes['. $store['value'] .']',
                                )
            );
        }

        if( Mage::getSingleton('adminhtml/session')->getRatingData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getRatingData());
            $data = Mage::getSingleton('adminhtml/session')->getRatingData()->getRatingCodes();
            if(isset($data['rating_codes'])) {
               $this->_setRatingCodes(Mage::getSingleton('adminhtml/session')->getRatingData()->getRatingCodes());
            }
            Mage::getSingleton('adminhtml/session')->setRatingData(null);
        } elseif ( Mage::registry('rating_data') ) {
            $form->setValues(Mage::registry('rating_data')->getData());
            if(Mage::registry('rating_data')->getRatingCodes()) {
               $this->_setRatingCodes(Mage::registry('rating_data')->getRatingCodes());
            }
        }

        if( Mage::registry('rating_data') ) {
            $collection = Mage::getModel('rating/rating_option')
                ->getResourceCollection()
                ->addRatingFilter(Mage::registry('rating_data')->getId())
                ->load();

            $i = 1;
            foreach( $collection->getItems() as $item ) {
                $fieldset->addField('option_code_' . $item->getId() , 'hidden', array(
                                        'required'  => true,
                                        'name'      => 'option_title[' . $item->getId() . ']',
                                        'value'     => ( $item->getCode() ) ? $item->getCode() : $i,
                                    )
                );
                $i ++;
            }
        } else {
            for( $i=1;$i<=5;$i++ ) {
                $fieldset->addField('option_code_' . $i, 'hidden', array(
                                        'required'  => true,
                                        'name'      => 'option_title[add_' . $i . ']',
                                        'value'     => $i,
                                    )
                );
            }
        }

        $fieldset = $form->addFieldset('visibility_form', array('legend'=>Mage::helper('rating')->__('Rating Visibility')));
        $fieldset->addField('stores', 'multiselect', array(
                                'label'     => Mage::helper('rating')->__('Visible In'),
                                'required'  => true,
                                'name'      => 'stores[]',
                                'values'    => $stores
                            )
        );

        if(Mage::registry('rating_data')) {
            $form->getElement('stores')->setValue(Mage::registry('rating_data')->getStores());
        }

        return parent::_prepareForm();
    }

    protected function _setRatingCodes($ratingCodes) {
        foreach($ratingCodes as $store=>$value) {
            if($element = $this->getForm()->getElement('rating_code_' . $store)) {
               $element->setValue($value);
            }
        }

    }

    public function toHtml()
    {
        return $this->_getWarningHtml() . parent::toHtml();
    }

    protected function _getWarningHtml()
    {
        return '<div>
<ul class="messages">
    <li class="notice-msg">
        <ul>
            <li>'.Mage::helper('rating')->__('If you do not specify an rating title for a store then the default value will be used.').'</li>
        </ul>
    </li>
</ul>
</div>';
    }


}