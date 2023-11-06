<?php

namespace Hiraeth\Templates;

use Hiraeth\Routing;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;

/**
 * {@inheritDoc}
 */
class TemplateResponder implements Routing\Responder
{
	/**
	 * A PSR-7 stream factory for creating streams
	 *
	 * @var StreamFactory|null
	 */
	protected $streams = NULL;


	/**
	 * Create a new instance of the responder
	 */
	public function __construct(StreamFactory $streams)
	{
		$this->streams = $streams;
	}


	/**
	 * {@inheritDoc}
	 */
	public function __invoke(Routing\Resolver $resolver): Response
	{
		$template  = $resolver->getResult();
		$response  = $resolver->getResponse();
		$stream    = $this->streams->createStream($template->render());
		$mime_type = $resolver->getType($stream);

		return $response
			->withHeader('Content-Type', $mime_type)
			->withBody($stream)
		;
	}


	/**
	 * {@inheritDoc}
	 */
	public function match(Routing\Resolver $resolver): bool
	{
		return $resolver->getResult() instanceof Template;
	}
}
