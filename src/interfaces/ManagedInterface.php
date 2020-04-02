<?php

namespace Hiraeth\Templates;

/**
 * An interface for setting the session manager
 */
interface ManagedInterface
{
	/**
	 * Get the templates manager for this object
	 */
	public function getTemplatesManager(): ?Manager;


	/**
	 * Set the templates manager for this object
	 */
	public function setTemplatesManager(Manager $templates): self;
}
