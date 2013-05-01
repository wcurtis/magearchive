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
 * @package    Mage_SearchLucene
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_SearchLucene_Block_Results extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getResults(Zend_Controller_Request_Http $request)
    {
        $this->setTemplate('searchlucene/result.phtml');
        $query = $request->getParam('q', false);
        $queryEscaped = htmlspecialchars($query);

        Mage::registry('action')->getLayout()->getBlock('head.meta')->setTitle(Mage::helper('searchlucene')->__('Search results for: %s', $queryEscaped));

        $var = Mage::getBaseDir('var');
        $index_dir = $var . DS . 'search' . DS . 'index';
        $index = Zend_Search_Lucene::open($index_dir);
        $hits = $index->find($query);

        $this->assign('hits', $hits);
        $this->assign('query', $queryEscaped);
    }
}
