<?php
/**
* Salted passwords for Magento using Zend_Auth_Adapter_DbTable
* @author Matheus Mendes aka bigodines
* @date Januray, 2008
*/
class Mage_Admin_Model_Auth_Adapter extends Zend_Auth_Adapter_DbTable {

    /**
     * This overrides authenticate() and assume the credentialTreatment is md5(?)
     * @return Zend_Auth_Result
     */
    public function authenticate() {

        // create result array
        $authResult = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );

        // get username and salted passord for this user.
        $dbSelect = $this->_zendDb->select();
        $dbSelect->from($this->_tableName)
                    ->where($this->_zendDb->quoteIdentifier($this->_identityColumn) . '= ?', $this->_identity);

        // query for the identity
        try {
            $resultIdentities = $this->_zendDb->fetchRow($dbSelect->__toString());
        } catch (Exception $e) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('The supplied parameters to Zend_Auth_Adapter_DbTable failed to '
                                                . 'produce a valid sql statement, please check table and column names '
                                                . 'for validity.');
        }

        $valid = Mage::helper('core')->validateHash($this->_credential, $resultIdentities['password']);

        if ($valid) { // BINGO!

            $this->_resultRow = $resultIdentities;

            $authResult['code'] = Zend_Auth_Result::SUCCESS;
            $authResult['messages'][] = 'Authentication successful.';
            return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);

        } else { // FAILED
            $authResult['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $authResult['messages'][] = 'Supplied credential is invalid.';
            return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
        }

    }
}