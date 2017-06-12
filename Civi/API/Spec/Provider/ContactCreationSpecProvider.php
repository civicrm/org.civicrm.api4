<?php

namespace Civi\API\Spec\Provider;

use Civi\API\Actions;
use Civi\API\Spec\FieldSpec;
use Civi\API\Spec\RequestSpec;

class ContactCreationSpecProvider implements SpecProviderInterface {

  /**
   * @param RequestSpec $specification
   */
  public function modifySpec(RequestSpec $specification) {
    $this->addDedupeField($specification);
  }

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action) {
    return $entity === 'Contact' && $action === Actions::CREATE;
  }

  /**
   * @param RequestSpec $specification
   */
  protected function addDedupeField(RequestSpec $specification) {
    $dedupeField = $specification->getFieldSpecByName('dupe_check');

    if (!$dedupeField) {
      $dedupeField = new FieldSpec('dupe_check', 'Boolean');
    }

    $dedupeField
      ->setDescription('Throw error if contact create matches dedupe rule')
      ->setTitle('Check for Duplicates');

    $specification->addFieldSpec($dedupeField);
  }
}
