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
	 * Overloadable method to get template context from the class we're installed on
	 *
	 * @return array<string,mixed>
	 */
	protected function getTemplateContext(): array
	{
		return [];
	}

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

		$template = $this->templates->load($template_path);

		return $template->setAll([
			'this' => $template,
			...$this->getTemplateContext(),
			...$data
		]);
	}
}
