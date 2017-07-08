<?php

class CRM_Api4_Page_AJAX extends CRM_Core_Page {

  public function run() {
    $entity = $this->urlPath[3];
    $action = $this->urlPath[4];
    $params = CRM_Utils_Request::retrieve('params', 'String');
    if ($params) {
      $params = json_decode($params, TRUE);
    }
    else {
      $params = array();
    }
    CRM_Utils_System::setHttpHeader('Content-Type', 'application/json');
    echo json_encode($this->execute($entity, $action, $params));
    CRM_Utils_System::civiExit();
  }

  protected function execute($entity, $action, $params) {
    $params['checkPermissions'] = FALSE; // FIXME!
    $result = civicrm_api4($entity, $action, $params);
    return array(
      'entity' => $entity,
      'action' => $action,
      'values' => (array) $result,
    );
  }

}
