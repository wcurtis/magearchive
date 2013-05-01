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
    protected $_items = array();

    protected function _construct()
    {
        $this->setTemplate('page/html/head.phtml');
    }

    public function addCss($name, $params="")
    {
        $this->addItem('skin_css', $name, $params);
        return $this;
    }

    public function addJs($name, $params="")
    {
        $this->addItem('js', $name, $params);
        return $this;
    }

    public function addCssIe($name, $params="")
    {
        $this->addItem('skin_css', $name, $params, 'IE');
        return $this;
    }

    public function addJsIe($name, $params="")
    {
        $this->addItem('js', $name, $params, 'IE');
        return $this;
    }

    public function addItem($type, $name, $params=null, $if=null, $cond=null)
    {
        if ($type==='skin_css' && empty($params)) {
            $params = 'media="all"';
        }
        $this->_items[$type.'/'.$name] = array(
            'type'   => $type,
            'name'   => $name,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
       );
        return $this;
    }

    public function removeItem($type, $name)
    {
        unset($this->_items[$type.'/'.$name]);
        return $this;
    }

    public function getCssJsHtml()
    {
//        return '';
        $lines = array();
        $baseJs = Mage::getBaseUrl('js');
        $html = '';

        $script = '<script type="text/javascript" src="%s" %s></script>';
        $stylesheet = '<link type="text/css" rel="stylesheet" href="%s" %s></link>';
        $alternate = '<link rel="alternate" type="%s" href="%s" %s></link>';

        foreach ($this->_items as $item) {
            if (!is_null($item['cond']) && !$this->getData($item['cond'])) {
                continue;
            }
            $if = !empty($item['if']) ? $item['if'] : '';
            switch ($item['type']) {
                case 'js':
                    #$lines[$if]['other'][] = sprintf($script, $baseJs.$item['name'], $item['params']);
                    $lines[$if]['script'][] = $item['name'];
                    break;

                case 'js_css':
                    //proxying css will require real-time prepending path to all image urls, should we do it?
                    $lines[$if]['other'][] = sprintf($stylesheet, $baseJs.$item['name'], $item['params']);
                    #$lines[$if]['stylesheet'][] = $item['name'];
                    break;

                case 'skin_js':
                    $lines[$if]['other'][] = sprintf($script, $this->getSkinUrl($item['name']), $item['params']);
                    break;

                case 'skin_css':
                    $lines[$if]['other'][] = sprintf($stylesheet, $this->getSkinUrl($item['name']), $item['params']);
                    break;

                case 'rss':
                    $lines[$if]['other'][] = sprintf($alternate, 'application/rss+xml'/*'text/xml' for IE?*/, $item['name'], $item['params']);
                    break;
            }
        }

        foreach ($lines as $if=>$items) {
            if (!empty($if)) {
                $html .= '<!--[if '.$if.']>'."\n";
            }
            if (!empty($items['script'])) {
                $html .= sprintf($script, $baseJs.'proxy.php/x.js?f='.join(',',$items['script']), '')."\n";
            }
            if (!empty($items['stylesheet'])) {
                $html .= sprintf($stylesheet, $baseJs.'proxy.php/x.css?f='.join(',',$items['stylesheet']), '')."\n";
            }
            if (!empty($items['other'])) {
                $html .= join("\n", $items['other'])."\n";
            }
            if (!empty($if)) {
                $html .= '<![endif]-->'."\n";
            }
        }

        return $html;
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
