<?php


/**
 * BOS1402779 Aanmaken en vullen bestand met dubbele BSN nummers
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 Jun 2014
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_anna_zeepsop_dubbelbsn($params) {
  $annaZeepsopConfig = CRM_Annazeepsop_Config::singleton();
  CRM_Core_DAO::executeQuery('TRUNCATE TABLE '.$annaZeepsopConfig->_doubleBsnTable);
  /*
   * read all distinct BSN from custom table
   */
  $query = 'SELECT DISTINCT '.$annaZeepsopConfig->_doubleBsnBsnColumn
    .' AS bsn, entity_id FROM '.$annaZeepsopConfig->_doubleBsnCustomTable
    .' WHERE '.$annaZeepsopConfig->_doubleBsnBsnColumn.' != ""';
  $dao = CRM_Core_DAO::executeQuery($query);
  while ($dao->fetch()) {
    /*
     * write record to double bsn table if there is a double
     */
    processDoubleBsn($dao->bsn, $dao->entity_id);
  }
  $returnValues = array('is_error' => 0, 'message' => 'Vullen bestand dgw_bsn klaar');
  return civicrm_api3_create_success($returnValues, $params, 'AnnaZeepsop', 'DubbelBsn');
}
/**
 * Function to check if there is another contact with the BSN and write
 * record if so
 * 
 * @param string $bsn
 * @param int $contactId
 */
function processDoubleBsn($bsn, $contactId) {
  $annaZeepsopConfig = CRM_Annazeepsop_Config::singleton();
  $query = 'SELECT entity_id FROM '.$annaZeepsopConfig->_doubleBsnCustomTable
    .' WHERE entity_id != %1 AND '.$annaZeepsopConfig->_doubleBsnBsnColumn.' = %2';
  $params = array(1 => array($contactId, 'Positive'), 2 => array($bsn, 'String'));
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  while ($dao->fetch()) {
    writeDoubleBsn($contactId, $dao->entity_id, $bsn);
  }
}
/**
 * Function to write record to dgw_bsn
 * 
 * @param int $contactIdA
 * @param int $contactIdB
 * @param string $bsn
 */
function writeDoubleBsn($contactIdA, $contactIdB, $bsn) {
  $insertFields = array();
  $insertParams = array();
  if (!empty($contactIdA) && !empty($contactIdB)) {
    $insertFields[] = 'bsn_1 = %1';
    $insertParams[1] = array($bsn, 'String');
    $insertFields[] = 'bsn_2 = %2';
    $insertParams[2] = array($bsn, 'String');
    $paramCounter = 3;
    $contactA = civicrm_api3('Contact', 'Getsingle', array('id' => $contactIdA));
    $contactB = civicrm_api3('Contact', 'Getsingle', array('id' => $contactIdB));
    if ($contactA['contact_is_deleted'] == 0 && $contactB['contact_is_deleted'] == 0) {
      setInsertFields($contactA, $contactB, $insertFields, $insertParams, $paramCounter);
      if (!empty($insertFields)) {
        $query = 'INSERT INTO dgw_bsn SET '.implode(', ', $insertFields);
        CRM_Core_DAO::executeQuery($query, $insertParams);
      }
    }
  }
}
/**
 * Function to set insert fields for dgw_bsn
 * 
 * @param array $contactA
 * @param array $contactB
 * @param string $bsn
 * @return array $result;
 */
function setInsertFields($contactA, $contactB, &$fields, &$params, &$counter) {
  $queryFields = array('contact_id' => 'Positive', 'sort_name' => 'String', 'display_name' => 'String',
    'birth_date' => 'Date', 'street_address' => 'String', 'city' => 'String', 'postal_code' => 'String',
    'phone' => 'String', 'email' => 'String');
  
  foreach ($queryFields as $fieldName => $type) {
    $valueA = $contactA[$fieldName];
    $valueB = $contactB[$fieldName];
    $fields[] = $fieldName.'_1 = %'.$counter;
    $params[$counter] = setInsertParam($valueA, $type);
    $counter++;
    $fields[] = $fieldName.'_2 = %'.$counter;
    $params[$counter]= setInsertParam($valueB, $type);
    $counter++;
  }
}
/**
 * Function to create a line for insert
 * 
 * @param string $column
 * @param int $counter
 * @return string
 */
function setFieldLine($column, $counter) {
  $line = $column.' = %'.$counter;
  return $line;
}
/**
 * Funciton to create a param value for insert
 * 
 * @param type $value
 * @param string $type
 * @return array
 */
function setInsertParam($value, $type) {
  if ($type == 'Date') {
    return array(date('Ymd', strtotime($value)), $type);
  } else {
    return array($value, $type);
  }
}