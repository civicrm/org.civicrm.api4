<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Generic\Action\DAOCreate;
use Civi\Api4\OptionGroup;

class OptionValuePreCreationSubscriber extends PreCreationSubscriber {

  /**
   * @param DAOCreate $request
   */
  protected function modify(DAOCreate $request) {
    $this->setOptionGroupId($request);
  }

  /**
   * @param DAOCreate $request
   *
   * @return bool
   */
  protected function applies(DAOCreate $request) {
    return $request->getEntityName() === 'OptionValue';
  }

  /**
   * @param DAOCreate $request
   * @throws \API_Exception
   * @throws \Exception
   */
  private function setOptionGroupId(DAOCreate $request) {
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

    $request->addValue('option_group_id', $optionGroup->first()['id']);
  }

}
