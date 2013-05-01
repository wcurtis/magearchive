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
 * Convert profiles run block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Convert_Profile_Run extends Mage_Adminhtml_Block_Abstract
{
    public function getProfile()
    {
        return Mage::registry('current_convert_profile');
    }

    protected function _toHtml()
    {
        $profile = $this->getProfile();

        echo '<html><head>
    <style type="text/css">
    ul { list-style-type:none; padding:0; margin:0; }
    li { margin-left:0; border:solid #CCC 1px; margin:2px; padding:2px 2px 2px 2px; font:normal 12px sans-serif; }
    img { margin-right:5px; }
    </style>
    <title>'.($profile->getId() ? $this->htmlEscape($profile->getName()) : $this->__('No profile')).'</title>
</head><body>';
        echo '<ul>';
        echo '<li>';
        if ($profile->getId()) {
            echo '<img src="'.Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
            echo $this->__("Starting profile execution, please wait...");
        } else {
            echo '<img src="'.Mage::getDesign()->getSkinUrl('images/error_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
            echo $this->__("No profile loaded...");
        }
        echo '</li>';
        echo '</ul>';

        if ($profile->getId()) {

            echo '<ul>';

            ob_implicit_flush();

            $profile->run();
            foreach ($profile->getExceptions() as $e) {
                switch ($e->getLevel()) {
                    case Varien_Convert_Exception::FATAL:
                        $img = 'error_msg_icon.gif';
                        $liStyle = 'background-color:#FBB; ';
                        break;
                    case Varien_Convert_Exception::ERROR:
                        $img = 'error_msg_icon.gif';
                        $liStyle = 'background-color:#FDD; ';
                        break;
                    case Varien_Convert_Exception::WARNING:
                        $img = 'fam_bullet_error.gif';
                        $liStyle = 'background-color:#FFD; ';
                        break;
                    case Varien_Convert_Exception::NOTICE:
                        $img = 'fam_bullet_success.gif';
                        $liStyle = 'background-color:#DDF; ';
                        break;
                }
                echo '<li style="'.$liStyle.'">';
                echo '<img src="'.Mage::getDesign()->getSkinUrl('images/'.$img).'" class="v-middle"/>';
                echo $e->getMessage();
                if ($e->getPosition()) {
                    echo " <small>(".$e->getPosition().")</small>";
                }
                echo "</li>";
    //            if ($e->getLevel()===Varien_Convert_Exception::FATAL) {
    //                echo "<blockquote>";
    //                Mage::printException($e);
    //                echo "</blockquote>";
    //            }
            }
            /*
            echo '<li>';
            echo '<img src="'.Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
            echo $this->__("Finished profile execution.");
            echo '</li>';
            echo "</ul>";
            */
        }
        /* test */

        $sessionId = Mage::registry('current_dataflow_session_id');
        $total =
        $import = Mage::getResourceModel('dataflow/import');
        $total = $import->loadTotalBySessionId($sessionId);
        echo "<li>Total: {$total['cnt']}, Finished data:<span id='finish_data'>0</span></li>";
        $min = $total['min'];
        $max = $total['max'];
        $product = new Mage_Catalog_Model_Convert_Parser_Product();
        $adaptor = new Mage_Catalog_Model_Convert_Adapter_Product();
        $importData = Mage::getModel('dataflow/import');
        while ($min < $max) {
       //for ($i = $total['min']; $i <= $total['cnt'];  $i++) {
            $data = $import->loadBySessionId($sessionId, $min - 1);
            if ($data) foreach($data as $index => $imported) {
                //$importData = Mage::getModel('dataflow/import');
                $importData->load($imported['import_id']);
                if ($id = $importData->getId()) {
                    $min = $id;
                    //$product = new Mage_Catalog_Model_Convert_Parser_Product();
                    $product->setData(unserialize($importData->getValue()));
                    $product->parseTest();
                    $invetory = $product->getInventoryItems();
                    //$adaptor = new Mage_Catalog_Model_Convert_Adapter_Product();
                    $adaptor->setData($product->getData());

                    $adaptor->setInventoryItems($invetory);
                    $adaptor->saveTest();
                    echo '<script>document.getElementById("finish_data").innerHTML= '.$id.';</script>';
                    $importData->setStatus(1);
                    $importData->save();
                    //unset($product);
                    //unset($adaptor);
                    //  unset($importData);
                }

            }
            unset($data);
            $total = $import->loadTotalBySessionId($sessionId);
            $min = $total['min'];
        }

        unset($session_id);
        unset($import);

        echo '<li>';
        echo '<img src="'.Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
        echo $this->__("Finished profile execution.");
        echo '</li>';
        echo "</ul>";

        echo '</body></html>';
        exit;
    }
}