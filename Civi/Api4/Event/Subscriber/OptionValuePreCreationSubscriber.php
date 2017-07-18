<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Api\OptionGroupApi;
use Civi\Api4\Request;

class OptionValuePreCreationSubscriber extends PreCreationSubscriber {

  /**
   * @inheritdoc
   */
  protected function modify(Request $request) {
    $this->setOptionGroupId($request);
  }

  /**
   * @inheritdoc
   */
  protected function applies(Request $request) {
    return $request->getEntity() === 'OptionValue';
  }

  /**
   * @param Request $request
   *
   * @throws \Exception
   */
  private function setOptionGroupId(Request $request) {
    $optionGroupName = $request->get('option_group');
    if (!$optionGroupName || $request->get('option_group_id')) {
      return;
    }

    $optionGroup = OptionGroupApi::get() // todo make dependency
      ->setCheckPermissions(FALSE)
      ->addSelect('id')
      ->addWhere('name', '=', $optionGroupName)
      ->execute();

    if ($optionGroup->count() !== 1) {
      throw new \Exception('Option group name must match only a single group');
    }

    $request->set('option_group_id', $optionGroup->first()['id']);
  }
}
