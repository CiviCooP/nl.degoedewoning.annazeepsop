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
   /**
   * Upgrade 1001 - add table dgw_first_deleted
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @date 20 Nov 2014
   */
  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001 (add table dgw_first_deleted)');
    $this->executeSqlFile('sql/createAnnaZeepsop.sql');
    return TRUE;
  }
}
