<?php

namespace Hiraeth\Templates;

/**
 * An abstract template interface for bridging diverse template engines
 */
interface Template
{
	/**
	 * Get the template's data referred to by a given name
	 */
	public function get($name);


	/**
	 * Get all the template's data
	 */
	public function getAll(): array;


	/**
	 * Get the extension for the template
	 */
	public function getExtension(): string;


	/**
	 * Set the template's data referred to by a given name to the value
	 */
	public function set($name, $value): Template;


	/**
	 * Set all data in the given array on the template's data
	 */
	public function setAll(array $data): Template;


	/**
	 * Render the template
	 */
	public function render(): string;
}
