<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Request;

class ContactPreUpdateSubscriber extends AbstractPreCreationSubscriber {
  /**
   * @inheritdoc
   */
  protected function modify(Request $request) {
    $this->addDefaultUpdateValues($request);
  }

  /**
   * @inheritdoc
   */
  protected function applies(Request $request) {
    return $request->getEntity() === 'Contact' && $request->get('id');
  }

  /**
   * @inheritdoc
   */
  protected function addDefaultUpdateValues(Request $request) {
    $id = $request->get('id');
    $contactType = $request->get('contact_type');
    $contactId = $request->get('contact_id');

    if ($id && !$contactType) {
      $contactType = \CRM_Contact_BAO_Contact::getContactType($id);
      $request->set('contact_type', $contactType);
    }

    if ($id && !$contactId) {
      $request->set('contact_id', $id);
    }
  }
}
