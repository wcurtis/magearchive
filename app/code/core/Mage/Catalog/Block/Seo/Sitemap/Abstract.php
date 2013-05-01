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
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Site Map category block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @module     Catalog
 */
abstract class Mage_Catalog_Block_Seo_Sitemap_Abstract extends Mage_Core_Block_Template
{   
    public function __construct()
    {
        $this->setTemplate('catalog/seo/sitemap/content.phtml');
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'catalog.seo.pager');
        $pager->setAvailableLimit(array(50=>50));
		$pager->setCollection($this->getMapItemCollection());
        $pager->setShowPerPage(false);
        $this->setChild('pager', $pager);
        $this->getMapItemCollection()->load(); 
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }   
}
