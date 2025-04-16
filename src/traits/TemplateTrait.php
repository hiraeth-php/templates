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
	 * @var Template
	 */
	protected $template;


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

		$template = $this->templates->load($template_path, $data);

		$this->template->setAll([
			'this'     => $template,
			'request'  => $this->request,
			'response' => $this->response,
		]);
	}
}
