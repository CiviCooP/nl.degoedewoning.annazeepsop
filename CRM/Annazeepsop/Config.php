<?php
/**
 * Class configuration singleton
 * 
 * @client De Goede Woning (http://www.degoedewoning.nl)
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 Jun 2014
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to De Goede Woning <http://www.degoedewoning.nl> and CiviCRM under AGPL-3.0
 */
class CRM_Annazeepsop_Config {
  /*
   * singleton pattern
   */
  static private $_singleton = NULL;
  
  public $_doubleBsnTable = NULL;
  public $_doubleBsnCustomGroupName = NULL;
  public $_doubleBsnCustomGroupId = NULL;
  public $_doubleBsnCustomTable = NULL;
  public $_doubleBsnBsnColumn = NULL;
  /**
   * Constructor function
   */
  function __construct() {
    $this->setDoubleBsnTable('dgw_bsn');
    $this->setDoubleBsnCustom('Aanvullende_persoonsgegevens');
  }
  
  private function setDoubleBsnTable($tableName) {
    $this->_doubleBsnTable = trim($tableName);
  }
    
  private function setDoubleBsnCustom($customGroupName) {
    try {
      $customGroup = civicrm_api3('CustomGroup', 'Getsingle', array('name' => $customGroupName));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->_doubleBsnCustomGroupId = 0;
      $this->_doubleBsnCustomGroupName = '';
      $this->_doubleBsnCustomTable = '';
      $this->_doubleBsnBsnColumn = '';
      throw new Exception('Could not find a group with name '.$customGroupName
        .',  error from API CustomGroup Getsingle : '.$ex->getMessage());
    }
    $this->_doubleBsnCustomGroupName = $customGroup['name'];
    $this->_doubleBsnCustomGroupId = $customGroup['id'];
    $this->_doubleBsnCustomTable = $customGroup['table_name'];
    $this->setBsnColumn();
  }
  
  private function setBsnColumn() {
    $params = array(
      'custom_group_id' => $this->_doubleBsnCustomGroupId,
      'return' => 'column_name',
      'name' => 'BSN');
    try {
      $this->_doubleBsnBsnColumn = (civicrm_api3('CustomField', 'Getvalue', $params));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->_doubleBsnBsnColumn = '';
      throw new Exception('Could not find custom field with name BSN in custom group '
        .$this->_doubleBsnCustomGroupName.', error from API CustomField Getvalue :'.$ex->getMessage());
    }
  }
  
  /**
   * Function to return singleton object
   * 
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Annazeepsop_Config();
    }
    return self::$_singleton;
  }
}
