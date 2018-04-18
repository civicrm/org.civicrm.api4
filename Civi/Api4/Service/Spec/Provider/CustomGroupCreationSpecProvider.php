<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Action\Actions;
use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

/**
 * Class CustomGroupCreationSpecProvider.
 */
class CustomGroupCreationSpecProvider implements SpecProviderInterface
{
	/**
	 * @param RequestSpec $spec
	 *
	 * @return FieldSpec|null
	 */
	public function modifySpec(RequestSpec $spec)
	{
		return $spec->getFieldByName('extends')->setRequired(true);
	}

	/**
	 * @param string $entity
	 * @param string $action
	 *
	 * @return bool
	 */
	public function applies($entity, $action)
	{
		return 'CustomGroup' === $entity && Actions::CREATE === $action;
	}
}
