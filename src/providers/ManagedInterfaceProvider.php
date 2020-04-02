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
	 */
	public function __invoke(object $instance, Hiraeth\Application $app): object
	{
		$instance->setTemplatesManager($app->get(Manager::class));

		return $instance;
	}
}
