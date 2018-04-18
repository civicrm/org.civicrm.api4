<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Action\Create;
use Civi\Api4\OptionGroup;

/**
 * Class OptionValuePreCreationSubscriber.
 */
class OptionValuePreCreationSubscriber extends PreCreationSubscriber {

  /**
   * @param Create $request
   *
   * @return bool
   */
  protected function applies(Create $request) {
    return 'OptionValue' === $request->getEntity();
  }

  /**
   * @param Create $request
   *
   * @throws \API_Exception
   * @throws \Exception
   */
  protected function modify(Create $request) {
    $this->setOptionGroupId($request);
  }

  /**
   * @param Create $request
   *
   * @throws \API_Exception
   * @throws \Exception
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
    if (1 !== $optionGroup->count()) {
      throw new \Exception('Option group name must match only a single group');
    }
    $request->addValue('option_group_id', $optionGroup->first()['id']);
  }
}
