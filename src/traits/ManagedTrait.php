<?php

namespace Hiraeth\Templates;

/**
 * Enables setting templates manager
 */
trait ManagedTrait
{
	/**
	 * The templates manager
	 *
	 * @var Manager|null
	 */
	protected $templates = NULL;


	/**
	 * {@inheritDoc}
	 */
	public function getTemplatesManager(): ?Manager
	{
		return $this->templates;
	}


	/**
	 * {@inheritDoc}
	 */
	public function setTemplatesManager(Manager $templates): ManagedInterface
	{
		$this->templates = $templates;

		return $this;
	}
}
