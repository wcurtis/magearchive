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
 * @package    Mage_Permissions
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Admin_Model_Permissions_Roles extends Varien_Object {

    public function getResource()
    {
        return Mage::getResourceSingleton('admin/permissions_roles');
    }

    public function load($roleId)
    {
        $this->setData($this->getResource()->load($roleId));
        return $this;
    }

    public function save()
    {
        $this->getResource()->save($this);
        return $this;
    }

    public function update()
    {
        $this->getResource()->update($this);
        return $this;
    }

    public function delete()
    {
        $this->getResource()->delete($this);
        return $this;
    }

    public function getCollection()
    {
        return Mage::getResourceModel('admin/permissions_roles_collection');
    }

    public function getUsersCollection()
    {
        return Mage::getResourceModel('admin/permissions_roles_user_collection');
    }

    public function getResourcesTree()
    {
        return $this->_buildResourcesArray(null, null, null, null, true);
    }

    public function getResourcesList()
    {
        return $this->_buildResourcesArray();
    }

    public function getResourcesList2D()
    {
        return $this->_buildResourcesArray(null, null, null, true);
    }

    public function getRoleUsers()
    {
        return $this->getResource()->getRoleUsers($this);
    }
    
    protected function _buildResourcesArray(Varien_Simplexml_Element $resource=null, $parentName=null, $level=0, $represent2Darray=null, $rawNodes = false)
    {
        static $result;
        if (is_null($resource)) {
//            $config = new Varien_Simplexml_Config();
//            $config->loadFile(Mage::getModuleDir('etc', 'Mage_Admin').DS.'admin.xml');
//            $resource = $config->getNode("admin/acl/resources");
            $resource=Mage::getConfig()->getNode('adminhtml/acl/resources');
            $resourceName = null;
            $level = -1;
//            unset($config);
        } else {
            if ($resource->getName()!='title') {
                $resourceName = (is_null($parentName) ? '' : $parentName.'/').$resource->getName();
                if ($rawNodes) {
                    $resource->addAttribute("aclpath", $resourceName);
                }

                if ( is_null($represent2Darray) ) {
                    $result[$resourceName]['name']  = Mage::helper('adminhtml')->__((string)$resource->title);
                    $result[$resourceName]['level'] = $level;
                } else {
                    $result[] = $resourceName;
                }
            }
        }
        $children = $resource->children();
        if (empty($children)) {
            if ($rawNodes) {
                return $resource;
            } else {
                return $result;
            }
        }
        foreach ($children as $child) {
            $this->_buildResourcesArray($child, $resourceName, $level+1, $represent2Darray, $rawNodes);
        }
        if ($rawNodes) {
            return $resource;
        } else {
            return $result;
        }
    }

}
?>
