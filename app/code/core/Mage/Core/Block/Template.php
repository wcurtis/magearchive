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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base html block
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Block_Template extends Mage_Core_Block_Abstract
{
    /**
     * View scripts directory
     *
     * @var string
     */
    protected $_viewDir = '';

    /**
     * Assigned variables for view
     *
     * @var array
     */
    protected $_viewVars = array();

    public function __construct()
    {
        parent::__construct();
        $this->_baseUrl = Mage::getBaseUrl();
        $this->_jsUrl = Mage::getBaseUrl(array('_type'=>'js'));
    }

    /**
     * Set block template
     *
     * @param     string $templateName
     * @return    Mage_Core_Block_Template
     */
    public function setTemplate($templateName)
    {
        $this->setTemplateName($templateName);
        return $this;
    }

    /**
     * Assign variable
     *
     * @param   string|array $key
     * @param   mixed $value
     * @return  Mage_Core_Block_Template
     */
    public function assign($key, $value=null)
    {
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->assign($k, $v);
            }
        }
        else {
            $this->_viewVars[$key] = $value;
        }
        return $this;
    }

    /**
     * Set template location dire
     *
     * @param string $dir
     * @return Mage_Core_Block_Template
     */
    public function setScriptPath($dir)
    {
        $this->_viewDir = $dir;
        return $this;
    }

    public function getDirectOutput()
    {
        if ($this->getLayout()) {
            return $this->getLayout()->getDirectOutput();
        }
        return false;
    }

    /**
     * Retrieve block view from file (template)
     *
     * @param   string $fileName
     * @return  string
     */
    public function fetchView($fileName)
    {
        Varien_Profiler::start($fileName);

        extract ($this->_viewVars);
        $do = $this->getDirectOutput();

        if (!$do) {
            ob_start();
        }
        include $this->_viewDir.DS.$fileName;
        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        Varien_Profiler::stop($fileName);
        return $html;
    }

    /**
     * Render block
     *
     * @return string
     */
    public function renderView()
    {
        Varien_Profiler::start(__METHOD__);

        $this->setScriptPath(Mage::getBaseDir('design'));
        $params = array('_relative'=>true);
        if ($area = $this->getArea()) {
            $params['_area'] = $area;
        }

        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplateName(), $params);
        $html = $this->fetchView($templateName);
        Varien_Profiler::stop(__METHOD__);

        return $html;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * If returns false html is rendered empty and cache is not saved
     *
     * @return boolean
     */
    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    /**
     * Before assign child block actions
     *
     * @param string $blockName
     */
    protected function _beforeChildToHtml($blockName, $blockObject)
    {
        // before assign child block actions
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function toHtml()
    {
        if (!($html = $this->_loadCache())) {

            if (!$this->_beforeToHtml()) {
                return '';
            }

            if (!$this->getTemplateName()) {
                return '';
            }

            $html = $this->renderView();
            $this->_saveCache($html);
        }
        return $html;
    }

    /**
     * Retrieve HTML of template
     *
     * @param   string $tplName
     * @param   array $assign
     * @return  string
     */
    public function tpl($tplName, array $args=array())
    {
        $block = $this->getLayout()->createBlock('core/template');
        /* @var $block Mage_Core_Block_Template */
        foreach ($assign as $k=>$v) {
            $block->assign($k, $v);
        }
        return $block->setTemplate($tplName)->toHtml();
/*
        extract($args);
        ob_start();
        include $this->_viewDir.DS.$tplName;
        return ob_get_clean(); */
    }

    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    public function getJsUrl($fileName='')
    {
        return $this->_jsUrl.$fileName;
    }
}