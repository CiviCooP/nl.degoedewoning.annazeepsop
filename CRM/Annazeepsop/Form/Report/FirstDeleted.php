<?php

class CRM_Annazeepsop_Form_Report_FirstDeleted extends CRM_Report_Form {
  protected $_summary = NULL;
  protected $_customGroupGroupBy = FALSE; 
  
  function __construct() {
    $this->configure_report();
    $this->set_columns();
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Verwijderde personen uit First'));
    parent::preProcess();
  }

  function select() {
    $this->_select = 'SELECT *';
  }

  function from() {
    $this->_from = 'FROM dgw_first_deleted';
  }
  
  function where() {
    $this->_where = '';
  }

  function orderBy() {
    $this->_orderBy = 'ORDER BY contact_id_first';
  }

  function postProcess() {

    $this->beginPostProcess();

    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }
  
  function buildRows($sql, &$rows) {
    $dao = CRM_Core_DAO::executeQuery($sql);
    if (!is_array($rows)) {
      $rows = array();
    }
    $this->modifyColumnHeaders();
    while ($dao->fetch()) {
      $row = array();
      foreach ($this->_columnHeaders as $key => $value) {
        if (property_exists($dao, $key)) {
          $row[$key] = $dao->$key;
        }
      }
      $rows[] = $row;
    }
  }
  
  function modifyColumnHeaders() {
    $this->_columnHeaders['contact_id_first'] = array('title' => ts('FIRST: ID'), 'type' => CRM_Utils_Type::T_INT);
    $this->_columnHeaders['display_name_first'] = array('title' => ts('Naam'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['gender_first'] = array('title' => ts('Geslacht'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['birth_date_first'] = array('title' => ts('Geb. Datum'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['renter_first'] = array('title' => ts('Huurder?'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['main_renter_first'] = array('title' => ts('Hoofd?'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['start_date_first'] = array('title' => ts('Start'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['end_date_first'] = array('title' => ts('Eind'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['reason_first'] = array('title' => ts('Reden'), 'type' => CRM_Utils_Type::T_STRING);
    $this->_columnHeaders['contact_id'] = array('title' => ts('CIVICRM: ID'), 'type' => CRM_Utils_Type::T_INT);
    $this->_columnHeaders['reason_civicrm'] = array('title' => ts('Reden niet verwijderd'), 'type' => CRM_Utils_Type::T_STRING);
  }

  function alterDisplay(&$rows) {
    $entry_found = FALSE;
    foreach ($rows as $row_num => $row) {
      if (!empty($row['contact_id'])) {
        $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$row['contact_id'], $this->_absoluteUrl);
        $rows[$row_num]['contact_id_link'] = $url;
        $rows[$row_num]['contact_id_hover'] = 'Klik om contactoverzicht te bekijken';
        $entry_found = TRUE;
      }
      if ($row['renter_first'] == 1) {
        $rows[$row_num]['renter_first'] = 'J';
      } else {
        $rows[$row_num]['renter_first'] = 'N';
      }
      if ($row['main_renter_first'] == 1) {
        $rows[$row_num]['main_renter_first'] = 'J';
      } else {
        $rows[$row_num]['main_renter_first'] = 'N';
      }
      $rows[$row_num]['birth_date_first'] = $this->alter_date($row['birth_date_first']);
      $rows[$row_num]['start_date_first'] = $this->alter_date($row['start_date_first']);
      $rows[$row_num]['end_date_first'] = $this->alter_date($row['end_date_first']);
      
      if (empty($row['reason_civicrm'])) {
        $rows[$row_num]['reason_civicrm'] = 'Nog niet verwerkt';
      }
      if (!$entry_found) {
        break;
      }
    }
  }
  
  protected function alter_date($in_date) {
    CRM_Core_Error::debug('in_date', $in_date);
    $out_date = '';
    if (!empty($in_date) && $in_date != '1970-01-01') {
      $out_date = date('d-m-Y', strtotime($in_date));        
    }
    CRM_Core_Error::debug('out_date', $out_date);
    return $out_date;
  }
  
  protected function configure_report() {
    $this->_tagFilter = FALSE;
    $this->_groupFilter = FALSE;
    $this->_exposeContactID = FALSE;
    $this->__groupButtonName = NULL;
    $this->_add2groupSupported = FALSE;
  }
  
  protected function set_columns() {
    $this->_columns = array();
  }
}

