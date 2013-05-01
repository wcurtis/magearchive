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
class Mage_Page_Block_Html_Head extends Mage_Core_Block_Text
{
    protected $_additionalCssJs = array();

    public function toHtml()
    {
        $this->addText('<title>'.$this->getTitle().'</title>'."\n\t");
        $this->addText('<meta http-equiv="Content-Type" content="'.$this->getContentType().'"/>'."\n\t");
        $this->addText('<meta name="description" content="'.$this->getDescription().'"/>'."\n\t");
        $this->addText('<meta name="keywords" content="'.$this->getKeywords().'"/>'."\n\t");
        $this->addText('<meta name="robots" content="'.$this->getRobots().'"/>'."\n");
        $this->addText($this->getAdditionalCssJs());
        $this->addText($this->getChildHtml());

        return parent::toHtml();
    }

    public function addCss($name)
    {
        $this->_additionalCssJs['css'][] = $name;
        return $this;
    }

    public function addJs($name)
    {
        $this->_additionalCssJs['js'][] = $name;
        return $this;
    }

    public function addCssIe($name)
    {
        $this->_additionalCssJs['cssIe'][] = $name;
        return $this;
    }

    public function addJsIe($name)
    {
        $this->_additionalCssJs['jsIe'][] = $name;
        return $this;
    }

	public function removeItem($type, $name)
	{
		if (!isset($this->_additionalCssJs[$type])) {
			return $this;
		}
		$key = array_search($name, $this->_additionalCssJs[$type]);
		if (false===$key) {
			return $this;
		}
		unset($this->_additionalCssJs[$type][$key]);
		return $this;
	}

    public function getAdditionalCssJs()
    {
        $lines = '';
        if (isset($this->_additionalCssJs['css']) && is_array($this->_additionalCssJs['css'])) {
            foreach ($this->_additionalCssJs['css'] as $item) {
                $lines .= '<link rel="stylesheet" type="text/css" media="all" href="' . $this->getSkinUrl('css/' . $item) . '" ></link>' . "\n";
            }
        }
        if (isset($this->_additionalCssJs['cssIe']) && is_array($this->_additionalCssJs['cssIe'])) {
            foreach ($this->_additionalCssJs['cssIe'] as $item) {
                $lines .= '<!--[if IE]> <link rel="stylesheet" type="text/css" media="all" href="' . $this->getSkinUrl('css/' . $item) . '" ></link> <![endif]-->' . "\n";
            }
        }
        if (isset($this->_additionalCssJs['js']) && is_array($this->_additionalCssJs['js'])) {
            foreach ($this->_additionalCssJs['js'] as $item) {
                $lines .= '<script type="text/javascript" src="' . Mage::getBaseUrl(array('_type'=>'js')) . $item . '" ></script>' . "\n";
            }
        }
        if (isset($this->_additionalCssJs['jsIe']) && is_array($this->_additionalCssJs['jsIe'])) {
            foreach ($this->_additionalCssJs['jsIe'] as $item) {
                $lines .= '<!--[if IE]> <script type="text/javascript" src="' . Mage::getBaseUrl(array('_type'=>'js')) . $item . '" ></script> <![endif]-->' . "\n";
            }
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
            return $this->getDesignConfig('page/head/media_type');
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
            return $this->getDesignConfig('page/head/charset');
        }
        else {
            return $this->_charset;
        }
    }

    public function setTitle($title)
    {
        $this->_title = $title;
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
        return $this->getDesignConfig('page/head/title');
    }

    public function setDescription($description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getDescription()
    {
        if (!$this->_description) {
            $this->_description = $this->getDesignConfig('page/head/description');
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
            $this->_keywords = $this->getDesignConfig('page/head/keywords');
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
            $this->_robots = $this->getDesignConfig('page/head/robots');
        }
        return $this->_robots;
    }

}
