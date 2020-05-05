<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\API\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Set acting user for api request, overriding the current acting user
 */
class ActingUserSubscriber implements EventSubscriberInterface {

  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    return [
      Events::AUTHORIZE => [
        ['onApiAuthorize', 500],
      ],
      Events::RESPOND => [
        ['onApiRespond', 500],
      ],
      Events::EXCEPTION => [
        ['onApiRespond', 500],
      ],
    ];
  }

  /**
   * Change acting user.
   *
   * Push current user onto the stack.
   *
   * @param \Civi\API\Event\AuthorizeEvent $event
   */
  public function onApiAuthorize(\Civi\API\Event\AuthorizeEvent $event) {
    /* @var \Civi\Api4\Generic\AbstractAction $apiRequest */
    $apiRequest = $event->getApiRequest();
    if ($apiRequest['version'] == 4 && $apiRequest->getActingUser()) {
      \CRM_Core_Session::singleton()->overrideCurrentUser($apiRequest->getActingUser(), $apiRequest['id']);
      // Set logging contact if this is a write operation
      if (substr($apiRequest->getActionName(), 0, 3) != 'get') {
        \CRM_Core_DAO::executeQuery('SET @civicrm_user_id = %1', [1 => [(int) $apiRequest->getActingUser(), 'Integer']]);
      }
    }
  }

  /**
   * Pop user off the stack when request is finished.
   *
   * @param \Civi\API\Event\Event $event
   */
  public function onApiRespond(\Civi\API\Event\Event $event) {
    /* @var \Civi\Api4\Generic\AbstractAction $apiRequest */
    $apiRequest = $event->getApiRequest();
    if ($apiRequest['version'] == 4 && $apiRequest->getActingUser()) {
      \CRM_Core_Session::singleton()->restoreCurrentUser($apiRequest['id']);
      if (substr($apiRequest->getActionName(), 0, 3) != 'get') {
        \CRM_Core_DAO::executeQuery('SET @civicrm_user_id = %1', [1 => [(int) \CRM_Core_Session::getLoggedInContactID(), 'Integer']]);
      }
    }
  }

}
