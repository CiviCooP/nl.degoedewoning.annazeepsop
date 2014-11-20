<?php
class CRM_Annazeepsop_Form_Report_Suspects3 extends CRM_Report_Form {
  
  function __construct() {
		$this->_columns = array( );
    parent::__construct( );    
  }
  
  function preProcess() {
    parent::preProcess();
  }  

  function postProcess() {
    $this->beginPostProcess( );
		$query = 'SELECT * FROM dgw_suspects3 ORDER BY sort_name_1';

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
                       
    $this->buildRows ($query, $rows);
		$this->alterDisplay($rows);
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
