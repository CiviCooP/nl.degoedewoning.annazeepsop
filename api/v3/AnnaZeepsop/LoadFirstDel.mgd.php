<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:AnnaZeepsop.LoadFirstDel',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Verwijderde personen First laden',
      'description' => 'Verwijderde personen uit First laden in tussenbestand',
      'run_frequency' => 'Daily',
      'api_entity' => 'AnnaZeepsop',
      'api_action' => 'LoadFirstDel',
      'parameters' => '',
      'is_active' => 0
    ),
  ),
);