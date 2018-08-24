<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

class ActionScheduleCreationSpecProvider implements SpecProviderInterface {
  /**
   * @inheritDoc
   */
  public function modifySpec(RequestSpec $spec) {
    $spec->getFieldByName('title')->setRequired(TRUE);
    $spec->getFieldByName('mapping_id')->setRequired(TRUE);
    $spec->getFieldByName('entity_value')->setRequired(TRUE);
    $spec->getFieldByName('start_action_date')->setRequiredIf('!$absolute_date');
    $spec->getFieldByName('absolute_date')->setRequiredIf('!$start_action_date');
  }

  /**
   * @inheritDoc
   */
  public function applies($entity, $action) {
    return $entity === 'ActionSchedule' && $action === 'create';
  }

}
