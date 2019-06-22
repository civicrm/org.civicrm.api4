<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\RequestSpec;

class GetActionDefaultsProvider implements Generic\SpecProviderInterface {

  /**
   * @inheritDoc
   */
  public function modifySpec(RequestSpec $spec) {
    $isDeletedField = $spec->getFieldByName('is_deleted');
    if ($isDeletedField) {
      $isDeletedField->setDefaultValue('0');
    }
  }

  /**
   * @inheritDoc
   */
  public function applies($entity, $action) {
    return $action === 'get';
  }

}
