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
  $customId = _getCustomFieldPersoonsnummerFirst();
  define('PERSOONSNUMMER_CUSTOM_ID', $customId);
  $countTrashed = 0;
  $countReported = 0;
  $dao = CRM_Core_DAO::executeQuery('SELECT contact_id_first FROM dgw_first_deleted');
  while ($dao->fetch()) {
    _processContact($dao->contact_id_first, $countReported, $countTrashed);
  }
  $returnValues = array($countTrashed.' personen in de prullebak gegooid, '.$countReported.
    ' niet te verwijderen personen');
  return civicrm_api3_create_success($returnValues, $params, 'AnnaZeepsop', 'ProcessFirstDel');
}
/**
 * Function process contact 
 * 
 * @param int $firstId
 * @param int $countReported
 * @param int $countTrashed
 */
function _processContact($firstId, &$countReported, &$countTrashed) {
  $contactId = _getContactId($firstId);
  if (empty($contactId)) {
    $countReported++;
  } else {
    $activeEntities = _getActiveEntities($contactId);
    if (!empty($activeEntities)) {
      _updateFirstDeleted($contactId, $activeEntities, $firstId);
      $countReported++;
    } else {
      _trashContact($contactId, $firstId);
      $countTrashed++;
    }
  }
}
/**
 * Function to check if contact can be retrieved from CiviCRM and update if error
 * @param $firstId
 * @return int
 */
function _getContactId($firstId) {
  $reasonCivicrm = '';
  $contactId = _getCivicrmContact($firstId, $reasonCivicrm);
  if (!empty($reasonCivicrm)) {
    $update = 'UPDATE dgw_first_deleted SET reason_civicrm = %1 '
      . 'WHERE contact_id_first = %2';
    $params = array(
      1 => array($reasonCivicrm, 'String'),
      2 => array($firstId, 'Positive'));
    CRM_Core_DAO::executeQuery($update, $params);
    $contactId = 0;
  }
  return $contactId;
}
/**
 * Function to retrieve contact from CiviCRM
 * 
 * @param int $firstId
 * @param string $reasonCivicrm
 * @return int
 */
function _getCivicrmContact($firstId, &$reasonCivicrm) {
  $reasonCivicrm = '';
  $contactId = 0;
  $params = array(PERSOONSNUMMER_CUSTOM_ID => $firstId);
  $contactData = civicrm_api3('Contact', 'Get', $params);
  switch ($contactData['count']) {
    case 0:
      $reasonCivicrm = 'geen contact gevonden in civicrm met persoonsnummer First';
      break;
    case 1:
      foreach ($contactData['values'] as $contact) {
        $contactId = $contact['contact_id'];
        if ($contact['contact_type'] != 'Individual' && $contact['contact_type'] != 'Organization') {
          $reasonCivicrm = 'Contact type in civicrm is '.$contact['contact_type'];
        }
      }
      break;
    default:
      $reasonCivicrm = 'meer dan 1 contact gevonden met persoonsnummer first';
      break;
  }
  return $contactId;
}
/**
 * Function to get active elements for contact. Element can be 
 * huurovereenkomst, activity, group, case or relation
 * 
 * @param int $contactId
 * @return array $activeEntities
 */
function _getActiveEntities($contactId) {
  $activeEntities = array();
  _getActiveHuurovereenkomst($contactId, $activeEntities);
  _getActiveActivity($contactId, $activeEntities);
  _getActiveCase($contactId, $activeEntities);
  _getActiveGroup($contactId, $activeEntities);
  _getActiveRelation($contactId, $activeEntities);
  return $activeEntities;
}
/**
 * Function to get active huurovereenkomst for contact
 * 
 * @param int $contactId
 * @param array $activeEntities
 * @throws Exception when error from API
 */
function _getActiveHuurovereenkomst($contactId, &$activeEntities) {
  $activeEntity= array();
  $huishouden = _getHuishouden($contactId);
  if ($huishouden) {
    $hovTableName = CRM_Utils_DgwUtils::getDgwConfigValue("tabel huurovereenkomst huishouden");
    $customGroupParams = array('name' => $hovTableName);
    try {
      $hovTable = civicrm_api3('CustomGroup', 'Getsingle', $customGroupParams);
      $endDateFieldParams = array(
        'custom_group_id' => $hovTable['id'],
        'name' => "Einddatum_HOV",
        'return' => "column_name"
      );
      try {
        $endDateColumn = civicrm_api3('CustomField', 'Getvalue', $endDateFieldParams);
        $query = "SELECT " . $endDateColumn . " FROM " . $hovTable['table_name'] . " WHERE entity_id = %1 ORDER BY ".$endDateColumn." DESC";
        $params = array(1 => array($huishouden['id'], "Integer"));
        $dao = CRM_Core_DAO::executeQuery($query, $params);
        while ($dao->fetch()) {
          if (empty($dao->$endDateColumn)) {
            $activeEntity = array('name' => 'hov', 'title' => 'actieve huurovereenkomst als '.$huishouden['type']);
          } else {
            $activeEntity = array('name' => 'hov', 'title' => 'beëindigde huurovereenkomst als '.$huishouden['type']);
          }
        }
        $activeEntities[] = $activeEntity;
      } catch (CiviCRM_API3_Exception $ex) {
        throw new API_Exception("Geen uniek custom field gevonden met de naam Einddatum_HOV in tabel " . $hovTableName
          . ", neem contact op met de helpdesk met melding van API CustomField Getvalue: " . $ex->getMessage());
      }
    } catch (CiviCRM_API3_Exception $ex) {
      throw new API_Exception("Geen unieke custom tabel gevonden met de naam " . $hovTableName
        . ", neem contact op met de helpdesk met melding van API CustomGroup Getsingle: " . $ex->getMessage());
    }
  }
}
/**
 * Function to get activities for contact
 * 
 * @param int $contactId
 * @param array $activeEntities
 */
function _getActiveActivity($contactId, &$activeEntities) {
  $params = array(
    'contact_id' => $contactId,
    'is_current_revision' => 1);
  $countActivities = civicrm_api3('Activity', 'Getcount', $params);
  if ($countActivities > 0) {
      $activeEntity = array('name' => 'act', 'title' => 'activiteit');
      $activeEntities[] = $activeEntity;
  }
}
/**
 * Function to get cases for contact
 * 
 * @param int $contactId
 * @param array $activeEntities
 */
function _getActiveCase($contactId, &$activeEntities) {
  $countCases = civicrm_api3('Case', 'Getcount', array('contact_id' => $contactId));
  if ($countCases > 0) {
      $activeEntity = array('name' => 'case', 'title' => 'actief dossier');
      $activeEntities[] = $activeEntity;
  }  
}
/**
 * Function to get groups for contact
 * 
 * @param int $contactId
 * @param array $activeEntities
 */
function _getActiveGroup($contactId, &$activeEntities) {
  $countGroups = civicrm_api3('GroupContact', 'Getcount', array('contact_id' => $contactId));
  if ($countGroups > 0) {
      $activeEntity = array('name' => 'group', 'title' => 'lid van groep');
      $activeEntities[] = $activeEntity;
  }
}
/**
 * Function to get relations for contact
 * 
 * @param int $contactId
 * @param array $activeEntities
 */
function _getActiveRelation($contactId, &$activeEntities) {
  $relations = civicrm_api3('Relationship', 'Get', array('contact_id' => $contactId));
  if ($relations['count'] > 0) {
    $relationStatus = 'beëindigde';
    foreach ($relations['values'] as $relation) {
      if ($relation['is_active'] == 1) {
        $relationStatus = 'actieve';
      }
    }
    $activeEntity = array('name' => 'relation', 'title' => $relationStatus.' relatie');
    $activeEntities[] = $activeEntity;
  }
}
/**
 * Function to update record dgw_first_deleted with active elements
 * 
 * @param int $contactId
 * @param array $activeEntities
 * @param int $firstId
 */
function _updateFirstDeleted($contactId, $activeEntities, $firstId) {
  $entities = array();
  $reason = array();
  foreach ($activeEntities as $entity) {
    $entities[] = 'active_'.$entity['name'].' = %1';
    $reason[] = $entity['title'];
  }
  $update = 'UPDATE dgw_first_deleted SET '.implode(', ', $entities).', contact_id = %2, '
    . 'reason_civicrm = %3 WHERE contact_id_first = %4';
  $params = array(
    1 => array(1, 'Positive'),
    2 => array($contactId, 'Positive'),
    3 => array('Contact heeft '.implode(', ', $reason), 'String'),
    4 => array($firstId, 'Positive'));
  CRM_Core_DAO::executeQuery($update, $params);
}
/**
 * Function to trash contact and remopve from dgw_first_deleted
 * 
 * @param int $contactId
 */
function _trashContact($contactId, $firstId) {
  $trash = 'UPDATE civicrm_contact SET is_deleted = %1 WHERE id = %2';
  $trashParams = array(
    1 => array(1, 'Positive'),
    2 => array($contactId, 'Positive'));
  CRM_Core_DAO::executeQuery($trash, $trashParams);
  $delete = 'DELETE FROM dgw_first_deleted WHERE contact_id_first = %1';
  $deleteParams = array(
    1 => array($firstId, 'Positive'));
  CRM_Core_DAO::executeQuery($delete, $deleteParams);
}
/**
 * Function to get custom_id for persoonsnummer first
 * 
 * @return string $custom_field
 */
function _getCustomFieldPersoonsnummerFirst() {
  $customField = '';
  $fieldName = CRM_Utils_DgwUtils::getDgwConfigValue('persoonsnummer first');
  $field = CRM_Utils_DgwUtils::getCustomField(array('label' => $fieldName));
  if (!empty($field)) {
    $customField = 'custom_'.$field['id'];
  }
  return $customField;
}

/**
 * Function to get huishouden as hoofdhuurder or medehuurder
 * @param int $contactId
 * @return array|bool
 */
function _getHuishouden($contactId) {
  $query = 'SELECT contact_id_b FROM civicrm_relationship WHERE relationship_type_id = %1 AND contact_id_a = %2 ORDER BY end_date DESC';
  $paramsHoofd = array(
    1 => array(11, 'Integer'),
    2 => array($contactId, 'Integer')
  );
  $paramsMede = array(
    1 => array(13, 'Integer'),
    2 => array($contactId, 'Integer')
  );
  $daoHoofd = CRM_Core_DAO::executeQuery($query, $paramsHoofd);
  if ($daoHoofd->fetch()) {
    $huishouden = array(
      'id' => $daoHoofd->contact_id_b,
      'type' => "hoofdhuurder");
    return $huishouden;
  } else {
    $daoMede = CRM_Core_DAO::executeQuery($query, $paramsMede);
    if ($daoMede->fetch()) {
      $huishouden = array(
        'id' => $daoMede->contact_id_b,
        'type' => "medehuurder");
      return $huishouden;
    }
  }
  return FALSE;
}


