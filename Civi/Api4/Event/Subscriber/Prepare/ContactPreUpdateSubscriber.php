<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\ApiRequest;

class ContactPreUpdateSubscriber extends AbstractPreCreationSubscriber {
  /**
   * @inheritdoc
   */
  public function modify(ApiRequest $request) {
    $this->addDefaultUpdateValues($request);
  }

  /**
   * @inheritdoc
   */
  public function applies(ApiRequest $request) {
    return $request->getEntity() === 'Contact' && $request->get('id');
  }

  /**
   * @inheritdoc
   */
  protected function addDefaultUpdateValues(ApiRequest $request) {
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
