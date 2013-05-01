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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_CatalogSearch_Block_Term extends Mage_Core_Block_Template 
{

    public function __construct()
    {
        parent::__construct();
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'catalogsearch.pager');
        $pager->setAvailableLimit(array(50=>50));
		$pager->setCollection($this->getTermCollection());
        $pager->setShowPerPage(false);
        $this->setChild('pager', $pager);
        $this->getTermCollection()->load(); 
        return $this;
    }
    
    public function getTermCollection()
    {
        $collection = $this->getData('term_collection');
        if (is_null($collection)) {
        	$collection =  Mage::getResourceModel('catalogsearch/query_collection')
                ->setPopularQueryFilter();		               				
            $this->setData('term_collection', $collection);
        }
        return $collection;
    }
    
    public function getPagerHtml()
    {
        return $this->_getChildHtml('pager');
    }
    
    public function getSearchUrl($obj)
	{
	    $url = Mage::getModel('core/url');
	    /*
	    * url encoding will be done in Url.php http_build_query
	    * so no need to explicitly called urlencode for the text
	    */
	    $url->setQueryParam('q', $obj->name);
	    return $url->getUrl('catalogsearch/result');	  
	}	
}