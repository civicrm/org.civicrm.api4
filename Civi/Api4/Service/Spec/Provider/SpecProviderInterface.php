<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\RequestSpec;

/**
 * Interface SpecProviderInterface.
 */
interface SpecProviderInterface
{
	/**
	 * @param RequestSpec $spec
	 */
	public function modifySpec(RequestSpec $spec);

	/**
	 * @param string $entity
	 * @param string $action
	 *
	 * @return bool
	 */
	public function applies($entity, $action);
}
