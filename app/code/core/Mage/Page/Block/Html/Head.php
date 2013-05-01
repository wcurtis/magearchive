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
 * @package    Mage_Page
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 */
class Mage_Page_Block_Html_Head extends Mage_Core_Block_Template
{
    protected $_additionalCssJs = array();

    protected function _construct()
    {
        $this->setTemplate('page/html/head.phtml');
    }

    public function addCss($name, $params="")
    {
        $this->addItem('css', $name, $params);
        return $this;
    }

    public function addJs($name, $params="")
    {
        $this->addItem('js', $name, $params);
        return $this;
    }

    public function addCssIe($name, $params="")
    {
        $this->addItem('css', $name, $params, 'IE');
        return $this;
    }

    public function addJsIe($name, $params="")
    {
        $this->addItem('js', $name, $params, 'IE');
        return $this;
    }

    public function addItem($type, $name, $params=null, $if=null)
    {
        if ($type==='css' && empty($params)) {
            $params = 'media="all"';
        }
        $this->_additionalCssJs[$type.'/'.$name] = array(
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if
       );
        return $this;
    }

	public function removeItem($type, $name)
	{
		unset($this->_additionalCssJs[$type.'/'.$name]);
		return $this;
	}

    public function getAdditionalCssJs()
    {
        $lines = '';

        foreach ($this->_additionalCssJs as $item) {
            if (!empty($item['if'])) {
                $lines .= '<!--[if '.$item['if'].']>';
            }

            switch ($item['type']) {
                case 'js':
                    $lines .= '<script type="text/javascript" src="'.Mage::getBaseUrl('js').$item['name'].'" '.$item['params'].'></script>';
                    break;

                case 'skinJs':
                    $lines .= '<script type="text/javascript" src="'.$this->getSkinUrl('js/'.$item['name']).'" '.$item['params'].'></script>';

                case 'css':
                    $lines .= '<link type="text/css" rel="stylesheet" href="'.$this->getSkinUrl('css/'.$item['name']).'" '.$item['params'].'></link>';
                    break;
            }
            if (!empty($item['if'])) {
                $lines .= '<![endif]-->';
            }
            $lines .= "\n";
        }
        return $lines;
    }

    public function setContentType($contentType)
    {
        $this->_contentType = $contentType;
        return $this;
    }

    public function getContentType()
    {
        if (!$this->_contentType) {
            return $this->getMediaType().'; charset='.$this->getCharset();
        }
        else {
            return $this->_contentType;
        }
    }

    public function setMediaType($mediaType)
    {
        $this->_mediaType = $mediaType;
        return $this;
    }

    public function getMediaType()
    {
        if (!$this->_mediaType) {
            return Mage::getStoreConfig('design/head/default_media_type');
        }
        else {
            return $this->_mediaType;
        }
    }

    public function setCharset($charset)
    {
        $this->_charset = $charset;
        return $this;
    }

    public function getCharset()
    {
        if (!$this->_charset) {
            return Mage::getStoreConfig('design/head/default_charset');
        }
        else {
            return $this->_charset;
        }
    }

    public function setTitle($title)
    {
        $this->_title = Mage::getStoreConfig('design/head/title_prefix').' '.$title
            .' '.Mage::getStoreConfig('design/head/title_suffix');
        return $this;
    }

    public function getTitle()
    {
        if (!$this->_title) {
            $this->_title = $this->getDefaultTitle();
        }
        return $this->_title;
    }

    public function getDefaultTitle()
    {
        return Mage::getStoreConfig('design/head/default_title');
    }

    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getDescription()
    {
        if (!$this->_description) {
            $this->_description = Mage::getStoreConfig('design/head/default_description');
        }
        return $this->_description;
    }

    public function setKeywords($keywords)
    {
        $this->_keywords = $keywords;
        return $this;
    }

    public function getKeywords()
    {
        if (!$this->_keywords) {
            $this->_keywords = Mage::getStoreConfig('design/head/default_keywords');
        }
        return $this->_keywords;
    }

    public function setRobots($robots)
    {
        $this->_robots = $robots;
        return $this;
    }

    public function getRobots()
    {
        if (!$this->_robots) {
            $this->_robots = Mage::getStoreConfig('design/head/default_robots');
        }
        return $this->_robots;
    }

}
