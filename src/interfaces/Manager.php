<?php

namespace Hiraeth\Templates;

/**
 * An abstract template manager interface for bridging diverse template engines
 */
interface Manager
{
	/**
	 * Determine whether or not a template is available
	 */
	public function has(string $path): bool;


	/**
	 * Load a template
	 */
	public function load(string $path, array $data = []): Template;
}
