<?php

namespace Civi\Api4\Event\Subscriber\Exception;

use Civi\Api4\Response;
use Civi\Api4\Event\Events;
use Civi\Api4\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceptionSubscriber implements EventSubscriberInterface {

  /**
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    return array(
      Events::EXCEPTION => 'onApiException'
    );
  }

  /**
   * @param ExceptionEvent $event
   */
  public function onApiException(ExceptionEvent $event) {

    if ($event->hasResponse()) {
      return;
    }

    $data = $this->formatException($event->getException(), $event->isDebug());
    $response = new Response($data);
    $event->setResponse($response);
  }

  /**
   * @param \Exception $e
   *   An unhandled exception.
   * @param bool $isDebug
   *
   * @return array
   *   Response data
   */
  protected function formatException($e, $isDebug) {
    $data = array();

    if ($e instanceof \PEAR_Exception) {
      $data = $this->formatPearException($e, $isDebug);
    }

    if ($isDebug) {
      $data['trace'] = $e->getTraceAsString();
    }
    $data['message'] = $e->getMessage();
    $data['code'] = $e->getCode();

    return $data;
  }

  /**
   * @param \PEAR_Exception $exception
   * @param bool $isDebug
   *
   * @return array
   */
  public function formatPearException(\PEAR_Exception $exception, $isDebug) {
    $data = array();
    $error = $exception->getCause();

    if ($error instanceof \DB_Error) {
      $data["error_code"] = \DB::errorMessage($error->getCode());
      $data["sql"] = $error->getDebugInfo();
    }
    if ($isDebug) {
      if (method_exists($exception, 'getUserInfo')) {
        $data['debug_info'] = $error->getUserInfo();
      }
      if (method_exists($exception, 'getExtraData')) {
        $data['debug_info'] = $data + $error->getExtraData();
      }
      $data['trace'] = $exception->getTraceAsString();
    }
    else {
      $data['tip'] = "add debug=1 to your API call to get more info.";
    }

    return $data;
  }

}
