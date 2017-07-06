<?php

namespace Civi\API\V4\Event\Subscriber;

use Civi\API\V4\Action\Create;
use Civi\API\V4\Entity\OptionGroup;

class OptionValuePreCreationSubscriber extends PreCreationSubscriber {

  /**
   * @param Create $request
   */
  protected function modify(Create $request) {
    $this->setOptionGroupId($request);
  }

  /**
   * @param Create $request
   *
   * @return bool
   */
  protected function applies(Create $request) {
    return $request->getEntity() === 'OptionValue';
  }

  /**
   * @param Create $request
   */
  private function setOptionGroupId(Create $request) {
    $optionGroupName = $request->getValue('option_group');
    if (!$optionGroupName || $request->getValue('option_group_id')) {
      return;
    }

    $optionGroup = OptionGroup::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('id')
      ->addWhere('name', '=', $optionGroupName)
      ->execute();

    if ($optionGroup->count() !== 1) {
      throw new \Exception('Option group name must match only a single group');
    }

    $request->setValue('option_group_id', $optionGroup->first()['id']);
  }
}
