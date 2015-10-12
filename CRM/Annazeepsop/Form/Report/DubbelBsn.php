<?php

class CRM_Annazeepsop_Form_Report_DubbelBsn extends CRM_Report_Form {
  function __construct() {
    $this->_columns = array();
    parent::__construct();   
  }
  
  function preProcess() {
    parent::preProcess();
  }
  
  function postProcess() {
		$this->beginPostProcess();

		$bsnQry = 'SELECT * FROM dgw_bsn';
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
                       
    $this->buildRows ($bsnQry, $rows);
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
