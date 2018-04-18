<?php

class CRM_Api4_Page_AJAX extends CRM_Core_Page
{

  /**
   * Handler for api4 ajax requests
   */
    public function run()
    {
        try {
          // Call multiple
            if (empty($this->urlPath[3])) {
                $calls = CRM_Utils_Request::retrieve('calls', 'String', CRM_Core_DAO::$_nullObject, true, null, 'REQUEST', true);
                $calls = json_decode($calls, true);
                $response = [];
                foreach ($calls as $index => $call) {
                    $response[$index] = $this->execute($call[0], $call[1], CRM_Utils_Array::value(2, $call, []));
                }
            } // Call single
            else {
                $entity = $this->urlPath[3];
                $action = $this->urlPath[4];
                $params = CRM_Utils_Request::retrieve('params', 'String');
                $params = $params ? json_decode($params, true) : [];
                $response = $this->execute($entity, $action, $params);
            }
        } catch (Exception $e) {
            http_response_code(500);
            $response = [
            'error_code' => $e->getCode(),
            ];
            if (CRM_Core_Permission::check('view debug output')) {
                $response['error_message'] = $e->getMessage();
                if (CRM_Core_BAO_Setting::getItem(null, 'backtrace')) {
                    $response['backtrace'] = $e->getTrace();
                }
            }
        }
        CRM_Utils_System::setHttpHeader('Content-Type', 'application/json');
        echo json_encode($response);
        CRM_Utils_System::civiExit();
    }

    /**
     * Run api call & prepare result for json encoding
     *
     * @param $entity
     * @param $action
     * @param $params
     *
     * @return array
     * @throws \API_Exception
     */
    protected function execute($entity, $action, $params)
    {
        $params['checkPermissions'] = true;
        $result = civicrm_api4($entity, $action, $params);
      // Convert arrayObject into something more suitable for json
        $vals = ['values' => (array) $result];
        foreach (get_class_vars(get_class($result)) as $key => $val) {
            $vals[$key] = $result->$key;
        }
        return $vals;
    }
}
