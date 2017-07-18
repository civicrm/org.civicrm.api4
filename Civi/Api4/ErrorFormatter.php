<?php

namespace Civi\Api4;

class ErrorFormatter {

  public function formatError(\Exception $e, $apiRequest) {
    if ($e instanceof \PEAR_Exception) {
      $err = $this->formatPearException($e, $apiRequest);
    }
    elseif ($e instanceof \API_Exception) {
      $err = $this->formatApiException($e, $apiRequest);
    }
    else {
      $err = $this->formatException($e, $apiRequest);
    }

    return $err;
  }

  /**
   * @param \Exception $e
   *   An unhandled exception.
   * @param array $apiRequest
   *   The full description of the API request.
   * @return array
   *   API response.
   */
  public function formatException($e, $apiRequest) {
    $data = array();
    if (!empty($apiRequest['params']['debug'])) {
      $data['trace'] = $e->getTraceAsString();
    }
    return $this->createError($e->getMessage(), $data, $apiRequest, $e->getCode());
  }

  /**
   * @param \API_Exception $e
   *   An unhandled exception.
   * @param array $apiRequest
   *   The full description of the API request.
   * @return array
   *   (API response)
   */
  public function formatApiException($e, $apiRequest) {
    $data = $e->getExtraParams();
    $data['entity'] = \CRM_Utils_Array::value('entity', $apiRequest);
    $data['action'] = \CRM_Utils_Array::value('action', $apiRequest);

    if (\CRM_Utils_Array::value('debug', \CRM_Utils_Array::value('params', $apiRequest))
      && empty($data['trace']) // prevent recursion
    ) {
      $data['trace'] = $e->getTraceAsString();
    }

    return $this->createError($e->getMessage(), $data, $apiRequest, $e->getCode());
  }

  /**
   * @param \PEAR_Exception $e
   *   An unhandled exception.
   * @param array $apiRequest
   *   The full description of the API request.
   * @return array
   *   API response.
   */
  public function formatPearException($e, $apiRequest) {
    $data = array();
    $error = $e->getCause();
    if ($error instanceof \DB_Error) {
      $data["error_code"] = \DB::errorMessage($error->getCode());
      $data["sql"] = $error->getDebugInfo();
    }
    if (!empty($apiRequest['params']['debug'])) {
      if (method_exists($e, 'getUserInfo')) {
        $data['debug_info'] = $error->getUserInfo();
      }
      if (method_exists($e, 'getExtraData')) {
        $data['debug_info'] = $data + $error->getExtraData();
      }
      $data['trace'] = $e->getTraceAsString();
    }
    else {
      $data['tip'] = "add debug=1 to your API call to have more info about the error";
    }

    return $this->createError($e->getMessage(), $data, $apiRequest);
  }

  /**
   * @param string $msg
   *   Descriptive error message.
   * @param array $data
   *   Error data.
   * @param array $apiRequest
   *   The full description of the API request.
   * @param mixed $code
   *   Doesn't appear to be used.
   *
   * @throws \API_Exception
   * @return array
   *   Array<type>.
   */
  public function createError($msg, $data, $apiRequest, $code = NULL) {
    // FIXME what to do with $code?
    if ($msg == 'DB Error: constraint violation' || substr($msg, 0, 9) == 'DB Error:' || $msg == 'DB Error: already exists') {
      try {
        $fields = _civicrm_api3_api_getfields($apiRequest);
        _civicrm_api3_validate_foreign_keys($apiRequest['entity'], $apiRequest['action'], $apiRequest['params'], $fields);
      }
      catch (\Exception $e) {
        $msg = $e->getMessage();
      }
    }

    $data = civicrm_api3_create_error($msg, $data);

    if (isset($apiRequest['params']) && is_array($apiRequest['params']) && !empty($apiRequest['params']['api.has_parent'])) {
      $errorCode = empty($data['error_code']) ? 'chained_api_failed' : $data['error_code'];
      throw new \API_Exception('Error in call to ' . $apiRequest['entity'] . '_' . $apiRequest['action'] . ' : ' . $msg, $errorCode, $data);
    }

    return $data;
  }

}
