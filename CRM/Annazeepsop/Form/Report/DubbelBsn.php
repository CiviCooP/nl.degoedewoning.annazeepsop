<?php

class CRM_Annazeepsop_Form_Report_DubbelBsn extends CRM_Report_Form {
  function __construct() {
    $this->_columns = array();
    parent::__construct();   
  }
  
  function preProcess() {
    parent::preProcess();
  }
  
  
  function select() {
    $this->_select = "SELECT * ";
  }

  function from() {
    $this->_from = "FROM dgw_bsn bsn
      INNER JOIN civicrm_contact contact1 ON contact1.id = bsn.contact_id_1
      INNER JOIN civicrm_contact contact2 ON contact2.id = bsn.contact_id_2";
  }

  function where() {
    $this->_where = "WHERE contact1.is_deleted = 0 AND contact2.is_deleted = 0";
  }
  
  function postProcess() {
		$this->beginPostProcess();
        
        $sql = $this->buildQuery(TRUE);
        $this->_columnHeaders = array(
			'contact_id_1' => array('title' => 'ID Contact 1'),
      'display_name_1' => array('title' => 'Naam'),
      'bsn_1'	=> array('title' => 'BSN'),
      'street_address_1' => array('title' => 'Adres'),
      'postal_code_1' => array('title' => 'Postcode'),
      'city_1' => array('title' => 'Plaats'),
      'phone_1' => array('title' => 'Telefoon'),
      'email_1' => array('title' => 'Emailadres'),
      'birth_date_1' => array('title' => 'Geb. dat.'),
        'contact_id_2' => array('title' => 'ID Contact 2'),
      'display_name_2' => array('title' => 'Naam'),
      'bsn_2'	=> array('title' => 'BSN'),
      'street_address_2' => array('title' => 'Adres'),
      'postal_code_2' => array('title' => 'Postcode'),
      'city_2' => array('title' => 'Plaats'),
      'phone_2' => array('title' => 'Telefoon'),
      'email_2' => array('title' => 'Emailadres'),
      'birth_date_2' => array('title' => 'Geb. dat.'),);
                       
    $this->buildRows ($sql, $rows);
    $this->alterDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);    
  }
  
  function alterDisplay(&$rows) {
    $entryFound = false;
    foreach ($rows as $rowNum => $row) {
      // make count columns point to detail report
      // convert name to links
      if (array_key_exists('contact_id_1', $row)) {
        $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$row['contact_id_1']);
        $rows[$rowNum]['contact_id_1_link' ] = $url;
        $rows[$rowNum]['contact_id_1_hover'] = ts("Gegevens contact 1");
        $entryFound = true;
      }
      if ( array_key_exists( 'contact_id_2', $row ) ) {
        $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$row['contact_id_2']);
        $rows[$rowNum]['contact_id_2_link' ] = $url;
        $rows[$rowNum]['contact_id_2_hover'] = ts("Gegevens contact 2");
        $entryFound = true;
      }
      if ( array_key_exists( 'birth_date_1', $row ) ) {
				$rows[$rowNum]['birth_date_1'] = date('d-m-Y', strtotime($row['birth_date_1']));
			}  
      if ( array_key_exists( 'birth_date_2', $row ) ) {
				$rows[$rowNum]['birth_date_2'] = date('d-m-Y', strtotime($row['birth_date_2']));
			}
      if ( !$entryFound ) {
        break;
      }
    }
  }
}
