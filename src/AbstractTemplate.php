<?php

namespace Hiraeth\Templates;

/**
 *
 */
abstract class AbstractTemplate implements TemplateInterface
{
	/**
	 *
	 */
	protected $data = array();


	/**
	 *
	 */
	public function get($name)
	{
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}

		return NULL;
	}


	/**
	 *
	 */
	public function getAll(): array
	{
		return $this->data;
	}


	/**
	 *
	 */
	public function set($name, $value): TemplateInterface
	{
		$this->data[$name] = $value;

		return $this;
	}


	/**
	 *
	 */
	public function setAll(array $data): TemplateInterface
	{
		$this->data = $data + $this->data;

		return $this;
	}
}
