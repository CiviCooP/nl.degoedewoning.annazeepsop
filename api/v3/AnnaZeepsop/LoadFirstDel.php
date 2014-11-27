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
  $count_transferred = 0;
  $source_file = get_source_file();
  $csv_separator = check_separator($source_file);
  $sf = fopen($source_file, 'r');
  while (!feof($sf)) {
    $source_data = fgetcsv($sf, 0, $csv_separator);
    process_contact($source_data);
    $count_transferred++;
  }
  fclose($sf);
  unlink($source_file);
  $return_values = 'Verwijderde First Personen in CiviCRM geladen, '.$count_transferred.
    ' personen geladen.';
  return civicrm_api3_create_success($return_values, $params, 'AnnaZeepsop', 'LoadFirstDel');
}
/**
 * Function to add a contact to table dgw_first_deleted (if not exists)
 * 
 * @param type $data
 */
function process_contact($data) {
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
        4 => array(alter_date($data[3]), 'Date'),
        5 => array(transform_first_code($data[4]), 'Positive'),
        6 => array(transform_first_code($data[5]), 'Positive'),
        7 => array(alter_date($data[7]), 'Date'),
        8 => array(alter_date($data[8]), 'Date'),
        9 => array($data[9], 'String')        
      );
      CRM_Core_DAO::executeQuery($insert, $params);
    }
  }
}
function alter_date($in_date) {
  if (!empty($in_date)) {
    $out_date = date('Ymd', strtotime($in_date));
  } else {
    $out_date = '';
  }
  return $out_date;
}
/**
 * Function to transfor J/j or any other value to tinyint
 * @param string $first_code
 * @return int $code_in_civicrm;
 */
function transform_first_code($first_code) {
  if (strtolower($first_code) == 'j') {
    $code_in_civicrm = 1;
  } else {
    $code_in_civicrm = 0;
  }
  return $code_in_civicrm;
}
/**
 * Function to check if the contact already exists in dgw_first_deleted
 * 
 * @param int $contact_id_first
 * @return boolean
 */
function contact_exists($contact_id_first) {
  if (is_numeric($contact_id_first)) {
    $query = 'SELECT COUNT(*) AS contact_count FROM dgw_first_deleted WHERE contact_id_first = %1';
    $params = array(1 => array($contact_id_first, 'Positive'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->fetch()) {
      if ($dao->contact_count > 0) {
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
function get_source_file() {
  $source_file = CRM_Utils_DgwUtils::getDgwConfigValue('kov bestandsnaam').'first_deleted.csv';
  if (!file_exists($source_file)) {
    throw new API_Exception("Bronbestand $source_file niet gevonden, verwerken Verwijderde First Personen mislukt");
  }
  return $source_file;
}
/**
 * Function to check which csv separator to use. Assumption is that
 * separator is ';', if reading first record return record with only 
 * 1 field, then ',' should be used
 * 
 * @param string $source_file
 * @return string $csv_separator
 */
function check_separator($source_file) {
  $test_separator = fopen($source_file, 'r');
  /*
   * first test if semi-colon or comma separated, based on assumption that
   * it is semi-colon and it should be comma if I only get one record then
   */
  if ($test_row = fgetcsv($test_separator, 0, ';')) {
    if (!isset($test_row[1])) {
      $csv_separator = ",";
    } else {
      $csv_separator = ";";
    }
  }
  fclose($test_separator);
  return $csv_separator;  
}
