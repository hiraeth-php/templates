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
	public function has(string $template_path): bool;


	/**
	 *
	 */
	public function load(string $template_path, array $data = []): TemplateInterface;
}
