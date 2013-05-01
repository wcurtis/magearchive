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
 * Inline Translations PHP part
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Translate_Inline
{
    protected $_tokenRegex = '\{\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\}';
    protected $_content;
    protected $_isAllowed;

    public function isAllowed($storeId=null)
    {
        if (is_null($this->_isAllowed)) {
            if (Mage::getDesign()->getArea()==='adminhtml') {
                $active = Mage::getStoreConfigFlag('dev/translate_inline/active_admin', $storeId);
            } else {
                $active = Mage::getStoreConfigFlag('dev/translate_inline/active', $storeId);
            }

            $this->_isAllowed = $active && Mage::helper('core')->isDevAllowed($storeId);
        }
        return $this->_isAllowed;
    }

    public function processAjaxPost($translate)
    {
        if (!$this->isAllowed()) {
            return;
        }

        $resource = Mage::getResourceModel('core/translate_string');
        foreach ($translate as $t) {
            $resource->saveTranslate($t['original'], $t['custom']);
        }
    }

    public function processResponseBody(&$bodyArray)
    {
        if (!$this->isAllowed()) {
            return;
        }

        foreach ($bodyArray as $i=>$content) {
            $this->_content = $content;

            $this->_tagAttributes();
            $this->_specialTags();
            $this->_otherText();

            $bodyArray[$i] = $this->_content;
        }

        $baseJsUrl = Mage::getBaseUrl('js');
        $ajaxUrl = Mage::getUrl('core/ajax/translate', array('_secure'=>Mage::app()->getStore()->isCurrentlySecure()));
        $trigImg = Mage::getDesign()->getSkinUrl('images/fam_book_open.png');

        ob_start();

?>
<!-- script type="text/javascript" src="<?=$baseJsUrl?>prototype/effects.js"></script -->
<script type="text/javascript" src="<?=$baseJsUrl?>prototype/window.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$baseJsUrl?>prototype/windows/themes/default.css"/>
<link rel="stylesheet" type="text/css" href="<?=$baseJsUrl?>prototype/windows/themes/magento.css"/>

<script type="text/javascript" src="<?=$baseJsUrl?>mage/translate_inline.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$baseJsUrl?>mage/translate_inline.css"/>

<div id="translate-inline-trig"><img src="<?=$trigImg?>" alt="[TR]"/></div>
<script type="text/javascript">
    new TranslateInline('translate-inline-trig', '<?=$ajaxUrl?>');
</script>
<?
        $bodyArray[] = ob_get_clean();
    }

    protected function _tagAttributes()
    {
#echo __METHOD__;
        $nextTag = 0; $i=0;
        while (preg_match('#<([a-z]+)\s*?[^>]+?(('.$this->_tokenRegex.')[^/>]*?)+(/?(>))#i',
            $this->_content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
#echo '<xmp>'.print_r($tagMatch[0][0],1).'</xmp><hr/>';

            $next = 0;
            $tagHtml = $tagMatch[0][0];
            $trArr = array();

            while (preg_match('#'.$this->_tokenRegex.'#i',
                $tagHtml, $m, PREG_OFFSET_CAPTURE, $next)) {

                $trArr[] = '{shown:\''.htmlspecialchars($m[1][0]).'\','
                    .'translated:\''.htmlspecialchars($m[2][0]).'\','
                    .'original:\''.htmlspecialchars($m[3][0]).'\','
                    .'location:\'Tag attribute (ALT, TITLE, etc.)\','
                    .'scope:\''.htmlspecialchars($m[4][0]).'\'}';
                $tagHtml = substr_replace($tagHtml, $m[1][0], $m[0][1], strlen($m[0][0]));
                $next = $m[0][1];
            }

            if (preg_match('# translate="\[(.+?)\]"#i', $tagMatch[0][0], $m, PREG_OFFSET_CAPTURE)) {
                foreach ($trArr as $i=>$tr) {
                    if (strpos($m[1][0], $tr)!==false) {
                        unset($trArr[$i]);
                    }
                }
                array_unshift($trArr, $m[1][0]);
                $start = $tagMatch[0][1]+$m[0][1];
                $len = strlen($m[0][0]);
            } else {
                $start = $tagMatch[8][1];
                $len = 0;
            }

            $trAttr = ' translate="['.join(',', $trArr).']"';
            $tagHtml = preg_replace('#/?>$#', $trAttr.'$0', $tagHtml);


            $this->_content = substr_replace($this->_content, $tagHtml,
                $tagMatch[0][1], $tagMatch[9][1]+1-$tagMatch[0][1]);
            $nextTag = $tagMatch[0][1];
        }
    }

    protected function _specialTags()
    {
#echo __METHOD__;

        $nextTag = 0;

        $location = array(
            'script' => 'String in Javascript',
            'title' => 'Page title',
            'select' => 'Dropdown option',
            'button' => 'Button label',
            'a' => 'Link label',
        );

        while (preg_match('#<(script|title|select|button|a)(\s+[^>]*|)(>)#i',
            $this->_content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
#echo '<xmp>'.print_r($tagMatch[0][0],1).'</xmp><hr/>';

            $tagClosure = '</'.$tagMatch[1][0].'>';
            $tagLength = stripos($this->_content, $tagClosure, $tagMatch[0][1])-$tagMatch[0][1]+strlen($tagClosure);

            $next = 0;
            $tagHtml = substr($this->_content, $tagMatch[0][1], $tagLength);
            $trArr = array();

            while (preg_match('#'.$this->_tokenRegex.'#i',
                $tagHtml, $m, PREG_OFFSET_CAPTURE, $next)) {

                $trArr[] = '{shown:\''.htmlspecialchars($m[1][0]).'\','
                    .'translated:\''.htmlspecialchars($m[2][0]).'\','
                    .'original:\''.htmlspecialchars($m[3][0]).'\','
                    .'location:\''.$location[strtolower($tagMatch[1][0])].'\','
                    .'scope:\''.htmlspecialchars($m[4][0]).'\'}';

                $tagHtml = substr_replace($tagHtml, $m[1][0], $m[0][1], strlen($m[0][0]));

                $next = $m[0][1];
            }
            if (!empty($trArr)) {
                $trArr = array_unique($trArr);

                $tag = strtolower($tagMatch[1][0]);

                switch ($tag) {
                    case 'script': case 'title':
                        $tagHtml .= '<span class="translate-inline-'.$tag
                            .'" translate="['.join(',',$trArr).']">'.strtoupper($tag).'</span>';
                        break;
                }
                $this->_content = substr_replace($this->_content, $tagHtml, $tagMatch[0][1], $tagLength);

                switch ($tag) {
                    case 'select': case 'button': case 'a':
                        if (preg_match('# translate="\[(.+?)\]"#i', $tagMatch[0][0], $m, PREG_OFFSET_CAPTURE)) {
                            foreach ($trArr as $i=>$tr) {
                                if (strpos($m[1][0], $tr)!==false) {
                                    unset($trArr[$i]);
                                }
                            }
                            array_unshift($trArr, $m[1][0]);
                            $start = $tagMatch[0][1]+$m[0][1];
                            $len = strlen($m[0][0]);
                        } else {
                            $start = $tagMatch[3][1];
                            $len = 0;
                        }
                        $this->_content = substr_replace($this->_content,
                            ' translate="['.join(',',$trArr).']"', $start, $len);
                        break;
                }
            }

            $nextTag = $tagMatch[0][1]+10;
        }

    }

    protected function _otherText()
    {
#return;
#echo __METHOD__;
#echo "<xmp>".$this->_content."</xmp><hr/>";
#exit;
        $next = 0;
        while (preg_match('#('.$this->_tokenRegex.')#',
            $this->_content, $m, PREG_OFFSET_CAPTURE, $next)) {
#echo '<xmp>'.print_r($m[0][0],1).'</xmp><hr/>';

            $tr = '{shown:\''.htmlspecialchars($m[2][0]).'\','
                .'translated:\''.htmlspecialchars($m[3][0]).'\','
                .'original:\''.htmlspecialchars($m[4][0]).'\','
                .'location:\'Text\','
                .'scope:\''.htmlspecialchars($m[5][0]).'\'}';
            $spanHtml = '<span translate="['.$tr.']">'.$m[2][0].'</span>';

            $this->_content = substr_replace($this->_content, $spanHtml, $m[0][1], strlen($m[0][0]));
            $next = $m[0][1];
        }

    }
}