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
 * @package    Mage_Paypal
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Paypal_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
        return array(
            'quote_payment' => array(
                'table'=>'sales/quote',
                'attributes' => array(
                    'paypal_payer_id' => array(),
                    'paypal_payer_status' => array(),
                    'paypal_correlation_id' => array(),
                ),
            ),

            'order_payment' => array(
                'table'=>'sales/order',
                'attributes' => array(

                ),
            ),

            'invoice_payment' => array(
                'table'=>'sales/invoice',
                'attributes' => array(

                ),
            ),
        );
    }
}