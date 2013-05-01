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
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category form input image element
 *
 * @category   Varien
 * @package    Varien_Data
 */
class Varien_Data_Form_Element_Image extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }

    public function getElementHtml()
    {
        $html = '';

        if ($this->getValue()) {
            $url = $this->_getUrl();
            $html = '<a href="'.$url.'" target="_blank" onclick="imagePreview(\''.$this->getHtmlId().'_image\');return false;">
            <img src="'.$url.'" id="'.$this->getHtmlId().'_image" alt="'.$this->getValue().'" height="22" width="22" align="absmiddle" class="small-image-preview">
            </a>';
        }
        $this->setClass(null);
        $html.= parent::getElementHtml();
        $html.= $this->_getDeleteCheckbox();
        return $html;
    }

    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ($this->getValue()) {
            $html.= '<input type="checkbox" name="'.parent::getName().'[delete]" value="1" id="'.$this->getHtmlId().'_delete"/>';
            $html.= '<label class="normal" for="'.$this->getHtmlId().'_delete">'.__('Delete Image').'</label>';
        }
        return $html;
    }

    protected function _getUrl()
    {
        return $this->getValue();
    }

    public function getName()
    {
        return  $this->getData('name');
    }
}