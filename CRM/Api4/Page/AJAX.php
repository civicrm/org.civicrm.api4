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
    try {
      $response = $this->execute($entity, $action, $params);
    }
    catch (Exception $e) {
      http_response_code(500);
      $response = array(
        'error_code' => $e->getCode(),
        'error_message' => $e->getMessage(),
      );
      if (CRM_Core_Permission::check('view debug output')) {
        $response['backtrace'] = $e->getTrace();
      }
    }
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }

  protected function execute($entity, $action, $params) {
    $params['checkPermissions'] = TRUE;
    $result = civicrm_api4($entity, $action, $params);
    // Convert arrayObject into something more suitable for json
    $vals = array('values' => (array) $result);
    foreach (get_class_vars(get_class($result)) as $key => $val) {
      $vals[$key] = $result->$key;
    }
    return $vals;
  }

}
