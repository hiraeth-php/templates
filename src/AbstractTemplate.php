<?php

namespace Hiraeth\Templates;

use RuntimeException;

/**
 * An abstract template that provides basic data management
 */
abstract class AbstractTemplate implements Template
{
	/**
	 * The template data
	 *
	 * @var mixed[]
	 */
	protected $data = [];


	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string
	{
		return $this->render();
	}


	/**
	 * {@inheritDoc}
	 */
	public function block(string $name, array $data = []): static
	{
		throw new RuntimeException('Blocks are not supported on this template');
	}


	/**
	 * {@inheritDoc}
	 */
	public function get(string $name): mixed
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
	public function set(string $name, mixed $value): static
	{
		$this->data[$name] = $value;

		return $this;
	}


	/**
	 * {@inheritDoc}
	 */
	public function setAll(array $data): static
	{
		$this->data = $data + $this->data;

		return $this;
	}
}
