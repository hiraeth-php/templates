<?php

namespace Hiraeth\Templates;

/**
 * An abstract template interface for bridging diverse template engines
 */
interface Template
{
	/**
	 * Get the template's data referred to by a given name
	 *
	 * @param string $name The name of the data variable
	 * @return mixed The value of the data variable
	 */
	public function get(string $name);


	/**
	 * Get all the template's data
	 *
	 * @return mixed[] The value of all the data variables
	 */
	public function getAll(): array;


	/**
	 * Get the extension for the template
	 */
	public function getExtension(): string;


	/**
	 * Set the template's data referred to by a given name to the value
	 *
	 * @param string $name The name of the variable to set in data
	 * @param mixed $value The value of the variable to set in data
	 * @return self
	 */
	public function set(string $name, $value): Template;


	/**
	 * Set all data in the given array on the template's data
	 *
	 * @param mixed[] $data The full set of names and values to set in data
	 * @return self
	 */
	public function setAll(array $data): Template;


	/**
	 * Render the template
	 */
	public function render(): string;
}
