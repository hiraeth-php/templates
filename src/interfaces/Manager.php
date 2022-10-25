<?php

namespace Hiraeth\Templates;

/**
 * An abstract template manager interface for bridging diverse template engines
 */
interface Manager
{
	/**
	 * Determine whether or not a template is available
	 *
	 * @param string $path The path to the template
	 * @return bool Whether or not a template can be found at teh given path
	 */
	public function has(string $path): bool;


	/**
	 * Load a template
	 *
	 * @param mixed[] $data The full set of names and values to set in data on load
	 * @return Template The renderable template
	 */
	public function load(string $path, array $data = []): Template;
}
