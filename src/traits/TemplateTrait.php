<?php

namespace Hiraeth\Templates;

use RuntimeException;

/**
 * Enables rendering templates
 */
trait TemplateTrait
{
	/**
	 * @var Manager
	 */
	protected $templates;


	/**
	 * Get a loaded template with data
	 *
	 * @param array<string, mixed> $data
	 */
	protected function template(string $template_path, array $data = []): Template
	{
		if (!$this->templates) {
			throw new RuntimeException(sprintf(
				'Render is not supported, no implementation for "%s" is registered',
				Manager::class
			));
		}

		return $this->templates->load($template_path, $data + [
			'request' => $this->request
		]);
	}
}
