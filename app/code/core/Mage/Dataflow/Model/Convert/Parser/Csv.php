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
 * @package    Mage_Dataflow
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Convert csv parser
 *
 * @category   Mage
 * @package    Mage_Dataflow
 */
class Mage_Dataflow_Model_Convert_Parser_Csv extends Mage_Dataflow_Model_Convert_Parser_Abstract
{
    protected $_fields;

    protected $_mapfields = array();

    public function parse()
    {
        // fixed for multibyte characters
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        if ($fDel=='\\t') {
            $fDel = "\t";
        }


        if (Mage::app()->getRequest()->getParam('files')) {
            $path = Mage::app()->getConfig()->getTempVarDir().'/import/';
            $file = $path.Mage::app()->getRequest()->getParam('files');
            if (file_exists($file)) {
                $fh = fopen($file, "r");
            }
            else {
                return $this;
            }
        }
        else {
            $data = $this->getData();
            $fh = tmpfile();
            fwrite($fh, $data);
            fseek($fh, 0);
        }

        // fix for field mapping
        if ($mapfields = $this->getProfile()->getDataflowProfile()) {
            $this->_mapfields = array_values($mapfields['gui_data']['map'][$mapfields['entity_type']]['db']);
        } // end

        if (!$this->getVar('fieldnames') && !$this->_mapfields) {
            $this->addException('Please define field mapping', Mage_Dataflow_Model_Convert_Exception::FATAL);
            return;
        }

        if ($this->getVar('adapter') && $this->getVar('method')) {
            $adapter = Mage::getModel($this->getVar('adapter'));
        }

        $i = 0;
        while (($line = fgetcsv($fh, null, $fDel, $fEnc)) !== FALSE) {
            $row = $this->parseRow($i, $line);

            if (!$this->getVar('fieldnames') && $i == 0 && $row) {
                $i = 1;
            }

            if ($row) {
                $loadMethod = $this->getVar('method');
                $adapter->$loadMethod(compact('i', 'row'));
            }
            $i++;
        }

        return $this;
    }

    public function parseRow($i, $line)
    {
        if (sizeof($line) == 1) return false;

        if (0==$i) {
            if ($this->getVar('fieldnames')) {
                $this->_fields = $line;
                return;
            } else {
                foreach ($line as $j=>$f) {
//                    $this->_fields[$j] = 'column'.($j+1);
                    $this->_fields[$j] = $this->_mapfields[$j];
                }
            }
        }

        $resultRow = array();

        foreach ($this->_fields as $j=>$f) {
            $resultRow[$f] = isset($line[$j]) ? $line[$j] : '';
        }
        return $resultRow;
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
        $this->_fields = $this->getGridFields($data);
        $lines = array();

        if ($this->getVar('fieldnames')) {
            $line = array();
            foreach ($this->_fields as $f) {
                $v = isset($f) ? str_replace('\\', $fEsc.'\\', $f) : '';
                $line[] = str_replace('"', '\"', $v);
                //$line[] = $fEnc.str_replace(array('"', '\\'), array('\"', $fEsc.'\\'), $f).$fEnc;
            }
            $lines[] = join($fDel, $line);
        }
        foreach ($data as $i=>$row) {
//            $lines[] = $this->unparseRow(compact($i, $row));
            $lines[] = $this->unparseRow(compact('i', 'row'));
        }
        $result = join($lDel, $lines);
        $this->setData($result);

        return $this;
    }

    public function unparseRow($args)
    {
        $i = $args['i'];
        $row = $args['row'];

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        $fEsc = $this->getVar('escape', '\\');
        $lDel = "\r\n";

        if ($fDel=='\\t') {
            $fDel = "\t";
        }

        $line = array();
        foreach ($this->_fields as $f) {
            $v = isset($row[$f]) ? str_replace(array('"', '\\'), array($fEnc.'"', $fEsc.'\\'), $row[$f]) : '';
            $line[] = $fEnc.$v.$fEnc;
        }

        return join($fDel, $line);
    }

}
