<?php

namespace Hiraeth\Templates;

/**
 *
 */
interface TemplateManagerInterface
{
	/**
	 *
	 */
	public function load(string $template_path, array $data = []): TemplateInterface;
}
