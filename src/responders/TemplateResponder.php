<?php

namespace Hiraeth\Templates;

use Hiraeth\Routing;
use Hiraeth\Mime\MimeTypesInterface as MimeTypes;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;

/**
 *
 */
class TemplateResponder implements Routing\ResponderInterface
{
	/**
	 * A mime types service
	 *
	 * @var MimeTypes|null
	 */
	protected $mimeTypes = NULL;


	/**
	 * A PSR-7 stream factory for creating streams
	 *
	 * @var StreamFactory|null
	 */
	protected $streamFactory = NULL;


	/**
	 *
	 */
	public function __construct(MimeTypes $mime_types, StreamFactory $stream_factory)
	{
		$this->mimeTypes     = $mime_types;
		$this->streamFactory = $stream_factory;
	}


	/**
	 *
	 */
	public function __invoke(Routing\Resolver $resolver): Response
	{
		$template  = $resolver->getResult();
		$response  = $resolver->getResponse();
		$mime_type = $this->mimeTypes->getMimeType($template->getExtension());

		if ($template->get('error')) {
			$response = $response->withStatus(400);
		}

		return $response
			->withHeader('Content-Type', $mime_type ?: 'text/html')
			->withBody($this->streamFactory->createStream($template->render()))
		;
	}


	/**
	 *
	 */
	public function match(Routing\Resolver $resolver): bool
	{
		return $resolver->getResult() instanceof Template;
	}
}
