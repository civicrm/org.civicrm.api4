<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Action\Actions;
use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

/**
 * Class ActivityCreationSpecProvider.
 */
class ActivityCreationSpecProvider implements SpecProviderInterface {
	/**
	 * @param string $entity
	 * @param string $action
	 *
	 * @return bool
	 */
	public function applies($entity, $action) {
		return 'Activity' === $entity && Actions::CREATE === $action;
	}

	/**
	 * @param \Civi\Api4\Service\Spec\RequestSpec $spec
	 *
	 * @throws \Exception
	 */
	public function modifySpec(RequestSpec $spec) {
		$spec->getFieldByName('subject')->setRequired(true);
		$sourceContactField = new FieldSpec('source_contact_id', 'Integer');
		$sourceContactField->setRequired(true);
		$sourceContactField->setFkEntity('Contact');
		$spec->addFieldSpec($sourceContactField);
	}
}
