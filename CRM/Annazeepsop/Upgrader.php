<?php

/**
 * Collection of upgrade steps
 */
class CRM_Annazeepsop_Upgrader extends CRM_Annazeepsop_Upgrader_Base {
  public function install() {
    $this->executeSqlFile('sql/createAnnaZeepsop.sql');
  }
  public function uninstall() {
    $this->executeSqlFile('sql/dropAnnaZeepsop.sql');
  }
}
