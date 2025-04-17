<?php

namespace Hiraeth\Templates;

use Stringable;

/**
 * An abstract template interface for bridging diverse template engines
 */
interface Template extends Stringable
{
	/**
	 * Must be an alias to render()
	 */
	public function __toString(): string;


	/**
	 * Select a specific template block for rendering
	 *
	 * This method should throw an exception if blocks are not supported
	 *
	 * @param array<mixed> $data The full set of names and values to set in data on load
	 * @return static The object for method chaining
	 */
	public function block(string $name, array $data = []): static;


	/**
	 * Get the template's data referred to by a given name
	 *
	 * @param string $name The name of the data variable
	 * @return mixed The value of the data variable
	 */
	public function get(string $name): mixed;


	/**
	 * Get all the template's data
	 *
	 * @return array<mixed> The value of all the data variables
	 */
	public function getAll(): array;


	/**
	 * Get the filename extension for the template as a string
	 *
	 * @return string The template filename extension
	 */
	public function getExtension(): string;


	/**
	 * Set the template's data referred to by a given name to the value
	 *
	 * @param string $name The name of the variable to set in data
	 * @param mixed $value The value of the variable to set in data
	 * @return static The object for method chaining
	 */
	public function set(string $name, mixed $value): static;


	/**
	 * Set all data in the given array on the template's data
	 *
	 * @param array<mixed> $data The full set of names and values to set in data
	 * @return static The object for method chaining
	 */
	public function setAll(array $data): static;


	/**
	 * Render the template to a string
	 *
	 * @return string The rendered template
	 */
	public function render(): string;
}
