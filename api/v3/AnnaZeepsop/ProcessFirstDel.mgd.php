<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:AnnaZeepsop.ProcessFirstDel',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Verwijderde personen First verwerken',
      'description' => 'Verwijderde personen uit First verwerken in CiviCRM',
      'run_frequency' => 'Daily',
      'api_entity' => 'AnnaZeepsop',
      'api_action' => 'ProcessFirstDel',
      'parameters' => '',
    ),
  ),
);