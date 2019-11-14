<?php

namespace Hiraeth\Templates;

/**
 * An abstract template that provides basic data management
 */
abstract class AbstractTemplate implements Template
{
	/**
	 * The template data
	 *
	 * @var array
	 */
	protected $data = array();


	/**
	 * {@inheritDoc}
	 */
	public function get($name)
	{
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}

		return NULL;
	}


	/**
	* {@inheritDoc}
	 */
	public function getAll(): array
	{
		return $this->data;
	}


	/**
	 * {@inheritDoc}
	 */
	public function set($name, $value): Template
	{
		$this->data[$name] = $value;

		return $this;
	}


	/**
	 * {@inheritDoc}
	 */
	public function setAll(array $data): Template
	{
		$this->data = $data + $this->data;

		return $this;
	}
}
