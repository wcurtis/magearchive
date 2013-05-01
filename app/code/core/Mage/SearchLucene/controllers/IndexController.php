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


/**
 * Page Index Controller
 *
 */
class Mage_SearchLucene_IndexController extends Mage_Core_Controller_Front_Action
{


    function indexAction()
    {
        $var = Mage::getBaseDir('var');
        $index_dir = $var . DS . 'search' . DS . 'index';
        $index = Zend_Search_Lucene::open($index_dir);
        $indexSize = $index->count();
        $documents = $index->numDocs();
        echo "Index size : " . $indexSize ."<br>";
        echo "Documents : " . $documents ."<br>";
    }


    function searchAction()
    {

        $this->loadLayout();

        $searchQuery = $this->getRequest()->getParam('q', false);
        if ($searchQuery) {
            $this->getLayout()->getBlock('search.form.mini')->assign('query', $searchQuery);

            $searchResBlock = $this->getLayout()->createBlock('searchlucene/results', 'searchlucene.results');
            $searchResBlock->getResults($this->getRequest());

            $this->getLayout()->getBlock('content')->append($searchResBlock);
        }
        $this->renderLayout();
    }


    function buildindexAction()
    {
        set_time_limit(0);
        $var = Mage::getBaseDir('var');
        $index_dir = $var . DS . 'search' . DS . 'index';


        $index = Zend_Search_Lucene::open($index_dir);

        if ($index->count()) {
            return true;
        }

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('description')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('manufacturer')
            ->addAttributeToSelect('shoe_type');

        $collection->load();
        $types = Mage::getModel('catalog/product_attribute')
            ->loadByCode('shoe_type')
            ->getSource()
            ->getArrOptions();

        $type_data = array();
        foreach($types as $typ) {
            $type_data[$typ['value']] = $typ['label'];
        }

        $manufacturers = Mage::getModel('catalog/product_attribute')
            ->loadByCode('manufacturer')
            ->getSource()
            ->getArrOptions();

        $man_data = array();
        foreach($manufacturers as $man) {
            $man_data[$man['value']] = $man['label'];
        }

        foreach($collection as $product) {
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(Zend_Search_Lucene_Field::Text('url', $product->getProductUrl(), 'utf-8'));
            $doc->addField(Zend_Search_Lucene_Field::Text('type', 'product', 'utf-8'));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('name', $product->getName(), 'utf-8'));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('sku', $product->getSku(), 'utf-8'));
            if (!empty($man_data[$product->getManufacturer()])) {
                $doc->addField(Zend_Search_Lucene_Field::Keyword('manufacturer', $man_data[$product->getManufacturer()], 'utf-8'));
            }
            if (!empty($type_data[$product->getShoe_type()])) {
                $doc->addField(Zend_Search_Lucene_Field::Keyword('shoe_type', $type_data[$product->getShoe_type()], 'utf-8'));
            }
            $doc->addField(Zend_Search_Lucene_Field::UnStored('description', $product->getDescription(), 'utf-8'));
            $index->addDocument($doc);
        }
    }


    function createAction()
    {
        $var = Mage::getBaseDir('var');
        $index_dir = $var . DS . 'search' . DS . 'index';
        if (!is_dir($index_dir)) {
            $res = mkdir($index_dir, 0777, true);
            if (!$res) {
                Mage::log("Can't create index directory : ". $index_dir, Zend_Log::CRIT);
                throw  new Exception(Mage::helper('searchlucene')->__("Can't create index directory : %s", $index_dir));
            }
        } else {
            Mage::log("Index directory " .$index_dir. " is created", Zend_Log::NOTICE);
        }
        Zend_Search_Lucene::create($index_dir);
    }

}
