<?php

namespace Hiraeth\Templates;

/**
 *
 */
interface TemplateInterface
{
	/**
	 *
	 */
	public function get($name);


	/**
	 *
	 */
	public function getAll(): array;


	/**
	 *
	 */
	public function set($name, $value): TemplateInterface;


	/**
	 *
	 */
	public function setAll(array $data): TemplateInterface;


	/**
	 *
	 */
	public function render(): string;
}
