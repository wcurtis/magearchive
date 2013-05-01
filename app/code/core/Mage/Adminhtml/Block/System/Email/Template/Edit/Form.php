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
 * Adminhtml system template edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_System_Email_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
     /**
     * Constructor
     *
     * Initialize form
     */
    public function __construct()
    {
        parent::__construct();

    }


    /**
     * Prepare form for render
     */
    public function renderPrepare($template)
    {
        $form = new Varien_Data_Form();

        if($this->_request->isPost()) {
            $post = $this->_request->getPost();
            if (isset($post['template_id'])) {
                unset($post['template_id']);
            }

            if (isset($post['template_type'])) {
                unset($post['template_type']);
            }

            $template->addData($post);
        }

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('Template Information')));

        $fieldset->addField('code', 'text', array(
            'name'=>'code',
            'label' => Mage::helper('adminhtml')->__('Template Name'),
            'title' => Mage::helper('adminhtml')->__('Template Name'),
            'class' => 'required-entry',
            'required' => true,
             'value' => $template->getTemplateCode()
        ));

        $fieldset->addField('subject', 'text', array(
            'name'=>'subject',
            'label' => Mage::helper('adminhtml')->__('Template Subject'),
            'title' => Mage::helper('adminhtml')->__('Template Subject'),
            'class' => 'required-entry',
            'required' => true,
            'value' => $template->getTemplateSubject()
        ));
/*
        $fieldset->addField('sender_name', 'text', array(
            'name'=>'sender_name',
            'label' => Mage::helper('adminhtml')->__('Sender Name'),
            'title' => Mage::helper('adminhtml')->__('Sender Name'),
            'class' => 'required-entry',
            'required' => true,
            'value' => $template->getTemplateSenderName()
        ));

        $fieldset->addField('sender_email', 'text', array(
            'name'=>'sender_email',
            'label' => Mage::helper('adminhtml')->__('Sender Email'),
            'title' => Mage::helper('adminhtml')->__('Sender Email'),
            'class' => 'required-entry validate-email',
            'required' => true,
            'value' => $template->getTemplateSenderEmail()
        ));
*/
        $txtType = constant(Mage::getConfig()->getModelClassName('core/email_template') . '::TYPE_TEXT');

        $fieldset->addField('text', 'editor', array(
            'name'=>'text',
            'wysiwyg' => ($template->getTemplateType() != $txtType),
            'label' => Mage::helper('adminhtml')->__('Template Content'),
            'title' => Mage::helper('adminhtml')->__('Template Content'),
            'class'	=> 'required-entry',
            'required' => true,
            'theme' => 'advanced',
            'state' => 'html',
            'value' => $template->getTemplateText(),
           	'style' => 'width:98%; height: 600px;',
        ));

        if ($template->getId()) {
            // If edit add id
            $form->addField('id', 'hidden',
                array(
                    'name'  => 'id',
                    'value' => $template->getId()
                )
            );
        }

        if($values = Mage::getSingleton('adminhtml/session')->getData('email_template_form_data', true)) {
        	$form->setValues($values);
        }

        $this->setForm($form);

        return $this;
    }
}
