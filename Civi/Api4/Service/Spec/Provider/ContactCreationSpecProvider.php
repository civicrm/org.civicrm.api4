<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Action\Actions;
use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

class ContactCreationSpecProvider implements SpecProviderInterface
{

  /**
   * @param RequestSpec $spec
   */
    public function modifySpec(RequestSpec $spec)
    {
        $this->addDedupeField($spec);
        $spec->getFieldByName('contact_type')
        ->setRequired(true)
        ->setDefaultValue('Individual');
    }

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
    public function applies($entity, $action)
    {
        return $entity === 'Contact' && $action === Actions::CREATE;
    }

  /**
   * @param RequestSpec $specification
   */
    protected function addDedupeField(RequestSpec $specification)
    {
        $dedupeField = $specification->getFieldByName('dupe_check');

        if (!$dedupeField) {
            $dedupeField = new FieldSpec('dupe_check', 'Boolean');
        }

        $dedupeField
        ->setDescription('Throw error if contact create matches dedupe rule')
        ->setTitle('Check for Duplicates');

        $specification->addFieldSpec($dedupeField);
    }
}
