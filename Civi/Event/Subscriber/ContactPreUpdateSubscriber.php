<?php

namespace Civi\API\V4\Event\Subscriber;

use Civi\API\V4\Action\Create;

class ContactPreUpdateSubscriber extends PreCreationSubscriber {
  /**
   * @param Create $request
   */
  protected function modify(Create $request) {
    $this->addDefaultUpdateValues($request);
  }

  /**
   * @param Create $request
   *
   * @return bool
   */
  protected function applies(Create $request) {
    return $request->getEntity() === 'Contact' && $request->getValue('id');
  }

  /**
   * @param Create $request
   */
  protected function addDefaultUpdateValues(Create $request) {
    $id = $request->getValue('id');
    $contactType = $request->getValue('contact_type');
    $contactId = $request->getValue('contact_id');

    if ($id && !$contactType) {
      $contactType = \CRM_Contact_BAO_Contact::getContactType($id);
      $request->setValue('contact_type', $contactType);
    }

    if ($id && !$contactId) {
      $request->setValue('contact_id', $id);
    }
  }
}
