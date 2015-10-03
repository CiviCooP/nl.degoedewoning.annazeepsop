<?php
/**
 * AnnaZeepsop.LoadFirstDel API
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * 
 * Naar aanleiding van incident BOS1408772
 * 
 * Job haalt data uit bestand first_delete.csv op ingestelde import map
 * en plaatst deze in tabel dgw_first_deleted
 * 
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_anna_zeepsop_loadfirstdel($params) {
  set_time_limit(0);
  $countTransferred = 0;
  $sourceFile = _getSourceFile();
  $csvSeparator = _checkSeparator($sourceFile);
  $sf = fopen($sourceFile, 'r');
  while (!feof($sf)) {
    $sourceData = fgetcsv($sf, 0, $csvSeparator);
    _processContact($sourceData);
    $countTransferred++;
  }
  fclose($sf);
  unlink($sourceFile);
  $returnValues = 'Verwijderde First Personen in CiviCRM geladen, '.$countTransferred.
    ' personen geladen.';
  return civicrm_api3_create_success($returnValues, $params, 'AnnaZeepsop', 'LoadFirstDel');
}
/**
 * Function to add a contact to table dgw_first_deleted (if not exists)
 * 
 * @param array $data
 */
function _processContact($data) {
  if (!empty($data[0])) {
    if (contact_exists($data[0]) == FALSE) {
      $insert = 'INSERT INTO dgw_first_deleted SET contact_id_first = %1, display_name_first '
        .'= %2, gender_first = %3, birth_date_first = %4, renter_first = %5, '
        .'main_renter_first = %6, start_date_first = %7, end_date_first = %8, ' 
        .'reason_first = %9';
      $params = array(
        1 => array($data[0], 'Positive'),
        2 => array($data[1].' '.$data[6], 'String'),
        3 => array($data[2], 'String'),
        4 => array(_alterDate($data[3]), 'Date'),
        5 => array(_transformFirstCode($data[4]), 'Positive'),
        6 => array(_transformFirstCode($data[5]), 'Positive'),
        7 => array(_alterDate($data[7]), 'Date'),
        8 => array(_alterDate($data[8]), 'Date'),
        9 => array($data[9], 'String')        
      );
      CRM_Core_DAO::executeQuery($insert, $params);
    }
  }
}
function _alterDate($inDate) {
  if (!empty($inDate)) {
    $outDate = date('Ymd', strtotime($inDate));
  } else {
    $outDate = '';
  }
  return $outDate;
}
/**
 * Function to transform J/j or any other value to tinyint
 * @param string $first_code
 * @return int $code_in_civicrm;
 */
function _transformFirstCode($firstCode) {
  if (strtolower($firstCode) == 'j') {
    $codeInCivicrm = 1;
  } else {
    $codeInCivicrm = 0;
  }
  return $codeInCivicrm;
}
/**
 * Function to check if the contact already exists in dgw_first_deleted
 * 
 * @param int $contactIdFirst
 * @return boolean
 */
function _contactExists($contactIdFirst) {
  if (is_numeric($contactIdFirst)) {
    $query = 'SELECT COUNT(*) AS contactCount FROM dgw_first_deleted WHERE contact_id_first = %1';
    $params = array(1 => array($contactIdFirst, 'Positive'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->fetch()) {
      if ($dao->contactCount > 0) {
        return TRUE;
      }
    }
  }
  return FALSE;
}
/**
 * Function to get the source file name
 * 
 * @return string
 * @throws API_Exception when source_file not found
 */
function _getSourceFile() {
  $sourceFile = CRM_Utils_DgwUtils::getDgwConfigValue('kov bestandsnaam').'first_deleted.csv';
  if (!file_exists($sourceFile)) {
    throw new API_Exception("Bronbestand $sourceFile niet gevonden, verwerken Verwijderde First Personen mislukt");
  }
  return $sourceFile;
}
/**
 * Function to check which csv separator to use. Assumption is that
 * separator is ';', if reading first record return record with only 
 * 1 field, then ',' should be used
 * 
 * @param string $source_file
 * @return string $csv_separator
 */
function _checkSeparator($sourceFile) {
  $testSeparator = fopen($sourceFile, 'r');
  /*
   * first test if semi-colon or comma separated, based on assumption that
   * it is semi-colon and it should be comma if I only get one record then
   */
  if ($testRow = fgetcsv($testSeparator, 0, ';')) {
    if (!isset($testRow[1])) {
      $csvSeparator = ",";
    } else {
      $csvSeparator = ";";
    }
  }
  fclose($testSeparator);
  return $csvSeparator;
}
