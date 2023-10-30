<?php

namespace Hiraeth\Templates;

use Hiraeth\Routing;

/**
 * {@inheritDoc}
 */
class TemplateAdapter implements Routing\Adapter
{
	/**
	 * @var Manager|NULL
	 */
	protected $manager = NULL;


	/**
	 *
	 */
	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
	}


	/**
	 * {@inheritDoc}
	 */
	public function __invoke(Routing\Resolver $resolver): callable
	{
		return function() use ($resolver) {
			if ($this->manager->has($resolver->getRoute()->getTarget())) {
				return $this->manager->load($resolver->getRoute()->getTarget(), [
					'route'   => $resolver->getRoute(),
					'request' => $resolver->getRequest()
				]);
			}
		};
	}


	/**
	 * {@inheritDoc}
	 */
	public function match(Routing\Resolver $resolver): bool
	{
		if (is_string($target = $resolver->getRoute()->getTarget())) {
			return strpos($target, '@') === 0;
		}

		return FALSE;
	}
}
