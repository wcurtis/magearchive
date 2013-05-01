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


class Mage_Catalog_Import {
    
    protected $_data = array();
    protected $_products = null;
    
    public function __construct()
    {
        $this->_products = Mage::getResourceModel('catalog/product_collection');
    }
    
    public function loadCsv($fileName, $fieldMap)
    {
        $this->_rows = array();
        $fp = fopen($fileName, 'r');
        while ($row = fgetcsv($fp, 0, "\t", '"')) {
            for ($i=0, $l=sizeof($row); $i<$l; $i++) {
                if (empty($fieldMap[$i]) || empty($row[$i])) {
                    continue;
                }
                $data[$fieldMap[$i]] = stripslashes($row[$i]);
            }
            $this->_data[] = $data;
        }
        fclose($fp);
        return $this;
    }
    
    public function convert()
    {
        foreach ($this->_data as $data) {
            $product = $this->importProduct($data);
            if ($product) {
                $this->_products->addItem($product);
            }
        }
        return $this;
    }
    
    public function save()
    {
        print_r($this->_products);
        $this->_products->walk('save');
        return $this;
    }
    
    public function importProduct($data)
    {
        if (empty($data['name'])) {
            return false;
        }
        $product = Mage::getModel('catalog/product');
        
        $product->setSetId(1)->setTypeId(1);
        $product->addData($data);
        
        return $product;
    }
}