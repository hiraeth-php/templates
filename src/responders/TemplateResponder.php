<?php

namespace Hiraeth\Templates;

use Hiraeth\Routing;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;

/**
 *
 */
class TemplateResponder implements Routing\ResponderInterface
{
	/**
	 *
	 */
	protected $streamFactory = NULL;


	/**
	 *
	 */
	public function __construct(StreamFactory $stream_factory)
	{
		$this->streamFactory = $stream_factory;
	}


	/**
	 *
	 */
	public function __invoke(Routing\Resolver $resolver): Response
	{
		return $resolver->getResponse()
			->withHeader('Content-Type', 'text/html')
			->withBody($this->streamFactory->createStream($resolver->getResult()->render()))
		;
	}


	/**
	 *
	 */
	public function match(Routing\Resolver $resolver): bool
	{
		return $resolver->getResult() instanceof TemplateInterface;
	}
}
