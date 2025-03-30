<?php

namespace Hiraeth\Templates;

use ArrayObject;
use Hiraeth\Application;
use InvalidArgumentException;

class Mock
{
	/**
	 * @param array<string, ArrayObject>
	 */
	static $data = array();

	/**
	 * @param array<string>
	 */
	static $building = [];


	/**
	 * @var Application
	 */
	public $app;


	/**
	 * @var Manager
	 */
	public $templates;


	/**
	 *
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}


	/**
	 * @param array<string, mixed> $context
	 * @param ?callable $result
	 */
	public function __invoke(string|array $data): ArrayObject
	{
		if (is_string($data)) {
			$ref = $data;

			if (!isset(static::$data[$ref]) && !in_array($data, static::$building)) {
				static::$building[]  = $ref;
				static::$data[$data] = new ArrayObject();

				$this->app->get(Manager::class)->load(sprintf('@mocks/%s.twig', $ref))->render();

				if (!isset(static::$data[$ref]['__set'])) {
					throw new InvalidArgumentException(sprintf(
						'Referenced mock file "%s" does did not define a mock',
						$ref
					));
				}
			}

		} else {
			$ref = array_pop(static::$building);

			static::$data[$ref]->exchangeArray($data + ['__set' => TRUE]);
		}

		return static::$data[$ref];
	}
}
