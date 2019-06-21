<?php

namespace Hiraeth\Templates;

/**
 *
 */
interface ManagerInterface
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
