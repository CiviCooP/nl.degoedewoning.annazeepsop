<?php

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Annazeepsop_Form_Report_Suspects3 extends CRM_Report_Form {

  protected $_summary = NULL;
  protected $_emailField = FALSE;
  protected $_phoneField = FALSE;

  function __construct() {
    $this->_columns = array();

    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  function preProcess() {
    CRM_Utils_System::setTitle(ts('Dubbele contacten Anna Zeepsop 3e regel (dedupe regel ID 9)'));
    parent::preProcess();
  }

  function select() {
    $this->_select = "SELECT * ";
  }

  function from() {
    $this->_from = "FROM dgw_suspects3git statu suspects
      INNER JOIN civicrm_contact contact1 ON contact1.id = suspects.contact_id_1
      INNER JOIN civicrm_contact contact2 ON contact2.id = suspects.contact_id_2";
  }

  function where() {
    $this->_where = "WHERE contact1.is_deleted = 0 AND contact2.is_deleted = 0";
  }

  function postProcess() {

    $this->beginPostProcess();

    $sql = $this->buildQuery(TRUE);
    $this->_columnHeaders = array(
      'display_name_1' => array( 'title' => 'Naam 1e contact' ),
      'contact_id_1' => array( 'title' => 'ID' ),
      'bsn_1' => array( 'title' => 'BSN' ),
      'street_address_1' => array( 'title' => 'Adres' ),
      'phone_1' => array( 'title' => 'Telefoon' ),
      'email_1'	=> array( 'title' => 'Emailadres' ),
      'birth_date_1' => array( 'title' => 'Geb. dat.' ),
      'display_name_2' => array( 'title' => 'Naam 2e contact'),
      'contact_id_2' => array( 'title' => 'ID' ),
      'bsn_2' => array( 'title' => 'BSN' ),
      'street_address_2' => array( 'title' => 'Adres' ),
      'phone_2' => array( 'title' => 'Telefoon' ),
      'email_2' => array( 'title' => 'Emailadres' ),
      'birth_date_2' => array( 'title' => 'Geb. dat.' )
    );
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function alterDisplay(&$rows) {
    $entryFound = false;
    foreach ($rows as $rowNum => $row) {
      // make count columns point to detail report
      // convert name to links
      if (array_key_exists('display_name_1', $row)) {
        $rows[$rowNum]['display_name_1_link' ] = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$row['contact_id_1']);
        $rows[$rowNum]['display_name_1_hover'] = ts("Gegevens contact 1");
        $entryFound = true;
      }
      if ( array_key_exists( 'display_name_2', $row ) ) {
        $rows[$rowNum]['display_name_2_link' ] = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$row['contact_id_2']);
        $rows[$rowNum]['display_name_2_hover'] = ts("Gegevens contact 1");
        $entryFound = true;
      }
      if ( array_key_exists( 'birth_date_1', $row ) ) {
        if ( $row['birth_date_1'] === "1970-01-01" ) {
          $rows[$rowNum]['birth_date_1'] = "";
        } else {
          $rows[$rowNum]['birth_date_1'] = date( "d-m-Y", strtotime( $row['birth_date_1'] ) );
        }
      }
      if ( array_key_exists( 'birth_date_2', $row ) ) {
        if ( $row['birth_date_2'] === "1970-01-01" ) {
          $rows[$rowNum]['birth_date_2'] = "";
        } else {
          $rows[$rowNum]['birth_date_2'] = date( "d-m-Y", strtotime( $row['birth_date_2'] ) );
        }
      }
      if ( !$entryFound ) {
        break;
      }
    }
  }
}

