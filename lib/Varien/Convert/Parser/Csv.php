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
 * @category   Varien
 * @package    Varien_Convert
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Convert csv parser
 *
 * @category   Varien
 * @package    Varien_Convert
 */
class Varien_Convert_Parser_Csv extends Varien_Convert_Parser_Abstract
{
	public function parse()
    {
        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');

        if ($fDel=='\\t') {
            $fDel = "\t";
        }

        $fp = tmpfile();
        fputs($fp, $this->getData());
        fseek($fp, 0);

        $data = array();
        for ($i=0; $line = fgetcsv($fp, 4096, $fDel, $fEnc); $i++) {
            if (0==$i) {
                if ($this->getVar('fieldnames')) {
                    $fields = $line;
                    continue;
                } else {
                    foreach ($line as $j=>$f) {
                        $fields[$j] = 'column'.($j+1);
                    }
                }
            }
            $row = array();
            foreach ($fields as $j=>$f) {
                $row[$f] = $line[$j];
            }
            $data[] = $row;
        }
        fclose($fp);
        $this->setData($data);
        return $this;
    }

    public function unparse()
    {
        $csv = '';

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        $fEsc = $this->getVar('escape', '\\');
        $lDel = "\r\n";

        if ($fDel=='\\t') {
            $fDel = "\t";
        }

        $data = $this->getData();
        $fields = $this->getGridFields($data);
        $lines = array();

        if ($this->getVar('fieldnames')) {
            $line = array();
            foreach ($fields as $f) {
                $line[] = $fEnc.str_replace(array('"', '\\'), array($fEsc.'"', $fEsc.'\\'), $f).$fEnc;
            }
            $lines[] = join($fDel, $line);
        }
        foreach ($data as $i=>$row) {
            $line = array();
            foreach ($fields as $f) {
                $v = isset($row[$f]) ? str_replace(array('"', '\\'), array($fEsc.'"', $fEsc.'\\'), $row[$f]) : '';
                $line[] = $fEnc.$v.$fEnc;
            }
            $lines[] = join($fDel, $line);
        }
        $result = join($lDel, $lines);
        $this->setData($result);

        return $this;
    }
}