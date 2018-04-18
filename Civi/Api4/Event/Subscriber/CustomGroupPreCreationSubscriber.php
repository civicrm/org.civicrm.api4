<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Action\Create;

/**
 * Class CustomGroupPreCreationSubscriber.
 */
class CustomGroupPreCreationSubscriber extends PreCreationSubscriber
{
	/**
	 * @param Create $request
	 */
	protected function modify(Create $request)
	{
		$extends = $request->getValue('extends');
		$title = $request->getValue('title');
		$name = $request->getValue('name');

		if (is_string($extends)) {
			$request->addValue('extends', [$extends]);
		}

		if (null === $title && $name) {
			$request->addValue('title', $name);
		}
	}

	/**
	 * @param Create $request
	 *
	 * @return bool
	 */
	protected function applies(Create $request)
	{
		return 'CustomGroup' === $request->getEntity();
	}
}
