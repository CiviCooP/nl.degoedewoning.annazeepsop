<?php

/**
 * AnnaZeepsop.GetSuspects API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_anna_zeepsop_getsuspects_spec(&$spec) {
  $spec['rule_id']['api.required'] = 1;
}

/**
 * AnnaZeepsop.GetSuspects API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_anna_zeepsop_getsuspects($params) {
  set_time_limit(0);
  if (array_key_exists('rule_id', $params)) {
    /*
     * set table based on rule_id
     */
    $tableName = _setTableName($params['rule_id']);
    if (!empty($tableName)) {
      define('DGW_SUSPECT_TABLE', _setTableName($params['rule_id']));
      CRM_Core_DAO::executeQuery('TRUNCATE TABLE '.DGW_SUSPECT_TABLE);
      /*
       * get all possible dupes with rule
       */
      $dupes = CRM_Dedupe_Finder::dupes($params['rule_id']);
      foreach ($dupes as $dupe) {
        _writeSuspect($dupe[0], $dupe[1], $dupe[2]);
      }
      $returnValues = array('is_error' => 0, 'message' => 'Verwerking succesvol afgerond');
      return civicrm_api3_create_success($returnValues, $params, 'AnnaZeepsop', 'GetSuspects');
    } else {
      throw new API_Exception('Parameter rule_id kan alleen 7, 8 of 9 zijn', 0002);
    }
  } else {
    throw new API_Exception('Parameter rule_id moet aanwezig zijn', 0001);
  }
}
/**
 * Function to get table name
 */
function _setTableName($ruleId) {
  $tableName = '';
  switch ($ruleId) {
    case 7:
      $tableName = 'dgw_suspects1';
      break;
    case 8:
      $tableName = 'dgw_suspects2';
      break;
    case 9:
      $tableName = 'dgw_suspects3';
      break;
  }
  return $tableName;
}
/**
 * Function to write suspect
 * 
 * @param int $contactIdA
 * @param int $contactIdB
 * @param int $score
 */
function _writeSuspect($contactIdA, $contactIdB, $score) {
  $insertFields = array();
  $insertParams = array();
  if (!empty($contactIdA) && !empty($contactIdB)) {
    $insertFields[] = 'score = %1';
    $insertParams[1] = array($score, 'Positive');
    $paramCounter = 2;
    $contactA = civicrm_api3('Contact', 'Getsingle', array('id' => $contactIdA));
    $contactB = civicrm_api3('Contact', 'Getsingle', array('id' => $contactIdB));
    if ($contactA['is_deleted'] == 0 && $contactB['is_deleted'] == 0) {
      setInsertFields($contactA, $contactB, $insertFields, $insertParams, $paramCounter);
      if (!empty($insertFields)) {
        $query = 'INSERT INTO ' . DGW_SUSPECT_TABLE . ' SET ' . implode(', ', $insertFields);
        $result = CRM_Core_DAO::executeQuery($query, $insertParams);
      }
    }
  }
}
/**
 * Function to set insert fields for dgw_suspect
 * 
 * @param array $contactA
 * @param array $contactB
 * @param array $fields;
 * @return array $result;
 */
function setInsertFields($contactA, $contactB, &$fields, &$params, &$counter) {
  $queryFields = array('contact_id' => 'Positive', 'display_name' => 'String',
    'birth_date' => 'Date', 'street_address' => 'String', 'city' => 'String', 'postal_code' => 'String',
    'phone' => 'String', 'email' => 'String', 'phone_type_id' => 'Positive');
  
  foreach ($queryFields as $fieldName => $type) {
    $valueA = $contactA[$fieldName];
    $valueB = $contactB[$fieldName];
    if (!empty($valueA)) {
      $fields[] = $fieldName . '_1 = %' . $counter;
      $params[$counter] = setInsertParam($valueA, $type);
      $counter++;
    }
    if (!empty($valueB)) {
      $fields[] = $fieldName . '_2 = %' . $counter;
      $params[$counter] = setInsertParam($valueB, $type);
      $counter++;
    }
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
 * Function to create a param value for insert
 * 
 * @param mixed $value
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
