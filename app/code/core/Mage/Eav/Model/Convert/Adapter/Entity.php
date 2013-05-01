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
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Eav_Model_Convert_Adapter_Entity extends Varien_Convert_Adapter_Abstract
{
	protected $_filter = array();
	protected $_joinFilter = array();
	protected $_joinAttr = array();
	protected $_attrToDb;

    public function getStoreId()
    {
        $store = $this->getVar('store');
        if (is_numeric($store)) {
            return $store;
        }
        if (!$store || !Mage::getConfig()->getNode('stores/'.$store)) {
            $this->addException(Mage::helper('eav')->__('Invalid store specified'), Varien_Convert_Exception::FATAL);
        }
        return (int)Mage::getConfig()->getNode('stores/'.$store.'/system/store/id');
    }
    /**
     * @param $attrFilter - $attrArray['attrDB']   = ['like','eq','fromTo','dateFromTo]
     * @param $attrToDb	- attribute name to DB field
     * @return Mage_Eav_Model_Convert_Adapter_Entity
    */
    
    protected function _parseVars(){
        $var_filters = $this->getVars();
        $filters = array();
        foreach ($var_filters as $key=>$val) {
        	if(substr($key,0,6)==='filter'){
        		$keys = explode('/',$key,2);
        		$filters[$keys[1]] = $val;
        	}
        }
        return $filters;
    }
    
    public function setFilter($attrFilterArray,$attrToDb=null,$bind=null,$joinType=null){
        if(is_null($bind))$def_bind='entity_id';
        if(is_null($joinType))$joinType='LEFT';
        
        $this->_attrToDb=$attrToDb;
        $filters = $this->_parseVars();
    	foreach ($attrFilterArray as $key=>$type) {
    	    if(is_array($type)){
    	        if(isset($type['bind'])){
    	           $bind = $type['bind'];
    	        } else {
    	           $bind = $def_bind;
    	        }
    	        $type = $type['type'];
    	    }
    	    $keyDB = (isset($this->_attrToDb[$key])) ? $this->_attrToDb[$key] : $key;
            $exp = explode('/',$key);
    	    if(isset($exp[1])){
    	        if(isset($filters[$exp[1]])){
    	           $val = $filters[$exp[1]];
    	           $this->setJoinAttr(array(
        	           'attribute' => $keyDB,
                       'bind' => $bind,
                       'joinType' => $joinType
                    ));
    	        } else {
    	            $val = null;
    	        }
    	        $keyDB = str_replace('/','_',$keyDB);
    	    } else {
    	        $val = isset($filters[$key]) ? $filters[$key] : null;
    	    }
    	    if(is_null($val)){
    	        continue;
    	    }
    	    $attr = array();
    	    switch ($type){
                case 'eq':
                    $attr = array('attribute'=>$keyDB,'eq'=>$val);
                    break;
                case 'like':
                    $attr = array('attribute'=>$keyDB,'like'=>'%'.$val.'%');
                    break;
                case 'fromTo':
                    $attr = array('attribute'=>$keyDB,'from'=>$val['from'],'to'=>$val['to']);
                    break;
                case 'dateFromTo':
                    $attr = array('attribute'=>$keyDB,'from'=>$val['from'],'to'=>$val['to'],'date'=>true);
                    break;
                default:
                break;
            }
    	    $this->_filter[] = $attr;
    	}
    	return $this;
    }

    public function getFilter()
    {
        return $this->_filter;    
    }
    
    public function setJoinAttr($joinAttr)
    {
    	if(is_array($joinAttr)){
    		$joinArrAttr = array();
    		$joinArrAttr['attribute'] = isset($joinAttr['attribute']) ? $joinAttr['attribute'] : null;
    		$joinArrAttr['alias'] = isset($joinAttr['attribute']) ? str_replace('/','_',$joinAttr['attribute']):null;
    		$joinArrAttr['bind'] = isset($joinAttr['bind']) ? $joinAttr['bind'] : null;
    		$joinArrAttr['joinType'] = isset($joinAttr['joinType']) ? $joinAttr['joinType'] : null;
    		$joinArrAttr['storeId'] = isset($joinAttr['storeId']) ? $joinAttr['storeId'] : null;
    		$this->_joinAttr[] = $joinArrAttr;
    	}
    }
    
    public function load()
    {
    	if (!($entityType = $this->getVar('entity_type'))
            || !(Mage::getResourceSingleton($entityType) instanceof Mage_Eav_Model_Entity_Interface)) {
            $this->addException(Mage::helper('eav')->__('Invalid entity specified'), Varien_Convert_Exception::FATAL);
        }
        try {
            $collection = Mage::getResourceModel($entityType.'_collection');
            $collection->getEntity()
                ->setStore($this->getStoreId());
           			
           	if(isset($this->_joinAttr)&& is_array($this->_joinAttr)){
           		foreach ($this->_joinAttr as $val){
           			$collection->joinAttribute(
           				$val['alias'],
           				$val['attribute'],
           				$val['bind'],
           				null,
           				strtolower($val['joinType']),
           				$val['storeId']
           			);
          		}
           	}
           	$filterQuery = $this->_filter;
           	if(isset($filterQuery) && is_array($filterQuery)){
                foreach ($filterQuery as $val) {
                    $collection->addFieldToFilter(array($val));	
                }
           	    
           	}
           	$collection
                ->addAttributeToSelect('*')
                ->load();
            #print $collection->getSelect()->__toString().'<hr>';
            $this->addException(Mage::helper('eav')->__('Loaded '.$collection->getSize().' records'));
        } catch (Varien_Convert_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $this->addException(Mage::helper('eav')->__('Problem loading the collection, aborting. Error: %s', $e->getMessage()),
                Varien_Convert_Exception::FATAL);
        }
        $this->setData($collection);
        return $this;
    }

    public function save()
    {
        $collection = $this->getData();
        if ($collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
            $this->addException(Mage::helper('eav')->__('Entity collections expected'), Varien_Convert_Exception::FATAL);
        }

        $this->addException($collection->getSize().' records found.');

        if (!$collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
            $this->addException(Mage::helper('eav')->__('Entity collection expected'), Varien_Convert_Exception::FATAL);
        }
        try {
            $i = 0;
            foreach ($collection->getIterator() as $model) {
                $model->save();
                $i++;
            }
            $this->addException(Mage::helper('eav')->__("Saved ".$i." record(s)"));
        } catch (Varien_Convert_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $this->addException(Mage::helper('eav')->__('Problem saving the collection, aborting. Error: %s', $e->getMessage()),
                Varien_Convert_Exception::FATAL);
        }
        return $this;
    }
}