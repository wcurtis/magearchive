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
 * Alerts products admin grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Alerts extends Mage_Core_Block_Template 
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/tab/alert.phtml');
    }
    
    public function getAlerts()
    {
        return Mage::getSingleton('customeralert/config')
            ->getAlerts();
    }   
    
    protected function _prepareLayout()
    {
        $params = $this->getRequest()->getParams();
        $data['product_id'] = isset($params['id']) ? $params['id'] : 0;
        $data['store_id'] = isset($params['store']) ? $params['store'] : 0;
        
        if($data['store_id']){
            $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')
                ->setId('alertsBlockId');
            $messages = array();
            foreach ($this->getAlerts() as $key=>$val) {
                $alertModel = Mage::getSingleton('customeralert/config')->getAlertByType($key);
                $alertModel->setParamValues($data);
                $accordion->addItem($key, array(
                    'title'     => $val['label'],
                    'content'   => $this->getLayout()
                                    ->createBlock('adminhtml/catalog_product_edit_tab_alerts_customers',$key,array('id'=>$key))
                                    ->setModel($alertModel)
                                    ->loadCustomers(),
                    'open'      => false,
                ));
                if($alertModel->getAlertText()){
                    if(is_array($alertModel->getAlertText())){
                        foreach ($alertModel->getAlertText() as $val){
                            $messages[] = array('method'=>'notice','label'=>$val);
                        }
                    } else {
                        $messages[] = array('method'=>'notice','label'=>$alertModel->getAlertText());
                    }
                }
            }
            $this->setChild('accordion', $accordion);
            $this->setChild('addToQuery_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Notify Now'),
                        'onclick'   => "queue.add()",
                        'class'     => 'add'
                    )));
        }
        $message = $this->getLayout()->createBlock('core/messages');
        foreach ($messages as $mess) {
            $message->getMessageCollection()->add(Mage::getSingleton('core/message')->$mess['method']($mess['label']));
        }
        $this->setChild('message', $message);
        return parent::_prepareLayout();
    }
    
    public function getAddToQueryButtonHtml()
    {
        return $this->getChildHtml('addToQuery_button');
    }
    
    public function getAccordionHtml()
    {
        return $this->getChildHtml('accordion');
    }
    
    public function getMessageHtml()
    {
        return $this->getChildHtml('message');
    }
    
    public function getAddToQueueUrl()
    {
        $params = $this->getRequest()->getParams();
        $data['product_id'] = isset($params['id']) ? $params['id'] : 0;
        $data['store_id'] = isset($params['store']) ? $params['store'] : 0;
        
        return Mage::getUrl('*/catalog_product/addCustomersToAlertQueue',$data);
    }
    
}