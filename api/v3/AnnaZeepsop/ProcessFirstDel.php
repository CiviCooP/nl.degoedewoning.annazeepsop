<?php
/**
 * AnnaZeepsop.ProcessFirstDel API
 * 
 * Process records from dgw_first_deleted and see if they are individual or 
 * organization in CiviCRM. If so, trash if they do not have active huurovereenkomst,
 * activity, case, group membership or relation
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
set_time_limit(0);
function civicrm_api3_anna_zeepsop_processfirstdel($params) {
  $custom_id = get_custom_field_persoonsnummer_first();
  define('PERSOONSNUMMER_CUSTOM_ID', $custom_id);
  $count_trashed = 0;
  $count_reported = 0;
  $dao = CRM_Core_DAO::executeQuery('SELECT contact_id_first FROM dgw_first_deleted');
  while ($dao->fetch()) {
    process_contact($dao->contact_id_first, $count_reported, $count_trashed);
  }
  $return_values = array($count_trashed.' personen in de prullebak gegooid, '.$count_reported.
    ' niet te verwijderen personen');
  return civicrm_api3_create_success($return_values, $params, 'AnnaZeepsop', 'ProcessFirstDel');
}
/**
 * Function process contact 
 * 
 * @param int $first_id
 * @param int $count_reported
 * @param int $count_trashed
 */
function process_contact($first_id, &$count_reported, &$count_trashed) {
  $contact_id = get_contact_id($first_id);
  if (empty($contact_id)) {
    $count_reported++;
  } else {
    $active_entities = get_active_entities($contact_id);
    if (!empty($active_entities)) {
      update_first_deleted($contact_id, $active_entities, $first_id);
      $count_reported++;
    } else {
      trash_contact($contact_id, $first_id);
      $count_trashed++;
    }
  }
}
/**
 * Function to check if contact can be retrieved from CiviCRM and update if error
 * @param type $first_id
 * @return int
 */
function get_contact_id($first_id) {
  $reason_civicrm = '';
  $contact_id = get_civicrm_contact($first_id, $reason_civicrm);
  if (!empty($reason_civicrm)) {
    $update = 'UPDATE dgw_first_deleted SET reason_civicrm = %1 '
      . 'WHERE contact_id_first = %2';
    $params = array(
      1 => array($reason_civicrm, 'String'),
      2 => array($first_id, 'Positive'));
    CRM_Core_DAO::executeQuery($update, $params);
    $contact_id = 0;
  }
  return $contact_id;
}
/**
 * Function to retrieve contact from CiviCRM
 * 
 * @param int $first_id
 * @param string $reason_civicrm
 * @return int
 */
function get_civicrm_contact($first_id, &$reason_civicrm) {
  $reason_civicrm = '';
  $contact_id = 0;
  $params = array(PERSOONSNUMMER_CUSTOM_ID => $first_id);
  $contact_data = civicrm_api3('Contact', 'Get', $params);
  switch ($contact_data['count']) {
    case 0:
      $reason_civicrm = 'geen contact gevonden in civicrm';
      break;
    case 1:
      foreach ($contact_data['values'] as $contact) {
        $contact_id = $contact['contact_id'];
        if ($contact['contact_type'] != 'Individual' && $contact['contact_type'] != 'Organization') {
          $reason_civicrm = 'Contact type in civicrm is '.$contact['contact_type'];
        }
      }
      break;
    default:
      $reason_civicrm = 'meer dan 1 contact gevonden met persoonsnummer first';
      break;
  }
  return $contact_id;
}
/**
 * Function to get active elements for contact. Element can be 
 * huurovereenkomst, activity, group, case or relation
 * 
 * @param int $contact_id
 * @return array $active_entities
 */
function get_active_entities($contact_id) {
  $active_entities = array();
  get_active_huurovereenkomst($contact_id, $active_entities);
  get_active_activity($contact_id, $active_entities);
  get_active_case($contact_id, $active_entities);
  get_active_group($contact_id, $active_entities);
  get_active_relation($contact_id, $active_entities);
  return $active_entities;
}
/**
 * Function to get active huurovereenkomst for contact
 * 
 * @param int $contact_id
 * @param array $active_entities
 * @throws API_Exception if class CRM_Utils_DgwUtils does not exist
 */
function get_active_huurovereenkomst($contact_id, &$active_entities) {
  if (class_exists('CRM_Utils_DgwUtils')) {
    if (CRM_Utils_DgwUtils::checkContactHoofdhuurder($contact_id) == TRUE ||
      CRM_Utils_DgwUtils::checkContactMedehuurder($contact_id) == TRUE) {
      $active_entity = array('name' => 'hov', 'title' => 'huurovereenkomst');
      $active_entities[] = $active_entity;
    }
  } else {
    throw new API_Exception('Could not find class CRM_Utils_DgwUtils, can not '
      . 'complete scheduled job successfully');
  }
}
/**
 * Function to get activities for contact
 * 
 * @param type $contact_id
 * @param int $active_entities
 */
function get_active_activity($contact_id, &$active_entities) {
  $params = array(
    'contact_id' => $contact_id,
    'is_current_revision' => 1);
  $count_activities = civicrm_api3('Activity', 'Getcount', $params);
  if ($count_activities > 0) {
      $active_entity = array('name' => 'act', 'title' => 'activiteit');
      $active_entities[] = $active_entity;
  }
}
/**
 * Function to get cases for contact
 * 
 * @param type $contact_id
 * @param int $active_entities
 */
function get_active_case($contact_id, &$active_entities) {
  $count_cases = civicrm_api3('Case', 'Getcount', array('contact_id' => $contact_id));
  if ($count_cases > 0) {
      $active_entity = array('name' => 'case', 'title' => 'dossier');
      $active_entities[] = $active_entity;
  }  
}
/**
 * Function to get groups for contact
 * 
 * @param type $contact_id
 * @param int $active_entities
 */
function get_active_group($contact_id, &$active_entities) {
  $count_groups = civicrm_api3('GroupContact', 'Getcount', array('contact_id' => $contact_id));
  if ($count_groups > 0) {
      $active_entity = array('name' => 'group', 'title' => 'groep');
      $active_entities[] = $active_entity;
  }
}
/**
 * Function to get relations for contact
 * 
 * @param type $contact_id
 * @param int $active_entities
 */
function get_active_relation($contact_id, &$active_entities) {
  $count_relations = civicrm_api3('Relationship', 'Getcount', array('contact_id' => $contact_id));
  if ($count_relations > 0) {
      $active_entity = array('name' => 'relation', 'title' => 'relatie');
      $active_entities[] = $active_entity;
  }      
}
/**
 * Function to update record dgw_first_deleted with active elements
 * 
 * @param int $contact_id
 * @param array $active_entities
 * @param int $first_id
 */
function update_first_deleted($contact_id, $active_entities, $first_id) {
  $entities = array();
  $reason = array();
  foreach ($active_entities as $entity) {
    $entities[] = 'active_'.$entity['name'].' = %1';
    $reason[] = 'actieve '.$entity['title'];
  }
  $update = 'UPDATE dgw_first_deleted SET '.implode(', ', $entities).', contact_id = %2, '
    . 'reason_civicrm = %3 WHERE contact_id_first = %4';
  $params = array(
    1 => array(1, 'Positive'),
    2 => array($contact_id, 'Positive'),
    3 => array('Contact heeft '.implode(', ', $reason), 'String'),
    4 => array($first_id, 'Positive'));
  CRM_Core_DAO::executeQuery($update, $params);
}
/**
 * Function to trash contact and remopve from dgw_first_deleted
 * 
 * @param type $contact_id
 */
function trash_contact($contact_id, $first_id) {
  $trash = 'UPDATE civicrm_contact SET is_deleted = %1 WHERE id = %2';
  $trash_params = array(
    1 => array(1, 'Positive'),
    2 => array($contact_id, 'Positive'));
  CRM_Core_DAO::executeQuery($trash, $trash_params);
  $delete = 'DELETE FROM dgw_first_deleted WHERE contact_id_first = %1';
  $delete_params = array(
    1 => array($first_id, 'Positive'));
  CRM_Core_DAO::executeQuery($delete, $delete_params);
}
/**
 * Function to get custom_id for persoonsnummer first
 * 
 * @return string $custom_field
 */
function get_custom_field_persoonsnummer_first() {
  $custom_field = '';
  $field_name = CRM_Utils_DgwUtils::getDgwConfigValue('persoonsnummer first');
  $field = CRM_Utils_DgwUtils::getCustomField(array('label' => $field_name));
  if (!empty($field)) {
    $custom_field = 'custom_'.$field['id'];
  }
  return $custom_field;
}

