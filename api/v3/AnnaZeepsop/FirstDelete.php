<?php
/**
 * AnnaZeepsop.FirstDelete API
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * 
 * Naar aanleiding van incident BOS1408772
 * 
 * Job haalt data uit bestand first_delete.csv op ingestelde import map
 * en verwerkt contacten daaruit. Als contact als persoon of org bestaat in 
 * CiviCRM en geen actieve elementen meer heeft, wordt contact naar de trash
 * gegooid.
 * 
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_anna_zeepsop_firstdelete($params) {
  set_time_limit(0);
  $count_trashed = 0;
  $count_reported = 0;
  $source_file = get_source_file();
  $csv_separator = check_separator($source_file);
  $sf = fopen($source_file, 'r');
  while (!feof($sf)) {
    $source_data = fgetcsv($sf, 0, $csv_separator);
    process_contact($source_data, $count_reported, $count_trashed);
  }
  fclose($sf);
  unlink($source_file);
  $return_values = 'Verwijderde First Personen verwerkt, '.$count_trashed.
    ' personen in de prullenbak gegooid, '.$count_reported.
    ' personen niet weg kunnen gooien (zie rapport).';
  return civicrm_api3_create_success($return_values, $params, 'AnnaZeepsop', 'FirstDelete');
}
/**
 * Function to process the contact 
 * 
 * @param array $source_data
 * @param int $count_reported
 * @param int $count_trashed
 */
function process_contact($source_data, &$count_reported, &$count_trashed) {
  if (is_to_be_trashed($source_data[0]) == TRUE ) {
    trash_contact($source_data[0]);
    $count_trashed++;
  } else {
    report_contact($source_data);
    $count_reported++;
  }
}
/**
 * Function to test if contact is to be trashed. False if contact does not
 * exist in civicrm as individual or organization and contact has no
 * active hov, case, activity, group or relation
 * 
 * @param type $contact_id_first
 * @return boolean
 */
function is_to_be_trashed($contact_id_first) {
  
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
