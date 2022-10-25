<?php

namespace Hiraeth\Templates;

use Hiraeth;

/**
 * {@inheritDoc}
 */
class ManagedInterfaceProvider implements Hiraeth\Provider
{
	/**
	 * {@inheritDoc}
	 */
	static public function getInterfaces(): array
	{
		return [
			ManagedInterface::class
		];
	}


	/**
	 * {@inheritDoc}
	 *
	 * @param ManagedInterface $instance The instance of the managed object
	 */
	public function __invoke(object $instance, Hiraeth\Application $app): object
	{
		$instance->setTemplatesManager($app->get(Manager::class));

		return $instance;
	}
}
