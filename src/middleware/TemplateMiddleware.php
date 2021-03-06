<?php

namespace Hiraeth\Templates;

use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;

/**
 * {@inheritDoc}
 */
class TemplateMiddleware implements Middleware
{
	/**
	 * The template manager
	 *
	 * @var Manager|null
	 */
	protected $manager = NULL;


	/**
	 * A PSR-7 stream factory for creating streams
	 *
	 * @var StreamFactory|null
	 */
	protected $streamFactory = NULL;


	/**
	 * Create a new instance of the middleware
	 */
	public function __construct(Manager $manager, StreamFactory $stream_factory)
	{
		$this->manager       = $manager;
		$this->streamFactory = $stream_factory;
	}


	/**
	 * {@inheritDoc}
	 */
	public function process(Request $request, RequestHandler $handler): Response
	{
		$response = $handler->handle($request);

		if ($response->getStatusCode() == 404) {
			$uri_path  = $request->getUri()->getPath();
			$is_dir    = substr($uri_path, -1, 1) == '/';

			if ($is_dir) {
				$path = '@pages' . $uri_path . 'index.html';
				$alt  = '@pages' . substr($uri_path, 0, -1) . '.html';

			} else {
				$path = '@pages' . $uri_path . '.html';
				$alt  = '@pages' . $uri_path . '/index.html';
			}

			$path = str_replace('/' . basename($path), '/@' . basename($path), $path);
			$alt  = str_replace('/' . basename($alt),  '/@' . basename($alt),  $alt);

			if ($this->manager->has($path)) {
				if ($is_dir || basename($path) != '@index.html') {
					$response = $response
						->withStatus(200)
						->withHeader('Content-Type', 'text/html; charset=utf-8')
						->withBody($this->streamFactory->createStream(
							$this->manager->load($path, ['request' => $request])->render()
						))
					;
				}

			} elseif ($this->manager->has($alt)) {
				$response = $response->withStatus(301);

				if ($is_dir) {
					$response = $response->withHeader(
						'Location',
						(string) $request->getUri()->withPath(substr($uri_path, 0, -1))
					);

				} else {
					$response = $response->withHeader(
						'Location',
						(string) $request->getUri()->withPath($uri_path . '/')
					);
				}
			}
		}

		return $response;
	}
}
