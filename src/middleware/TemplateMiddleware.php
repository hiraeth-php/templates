<?php

namespace Hiraeth\Templates;

use Dotink\Jin;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;

/**
 * {@inheritDoc}
 */
class TemplateMiddleware implements Middleware
{
	/**
	 * @var Jin\Parser
	 */
	protected $jin;

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
	public function __construct(Jin\Parser $jin, Manager $manager, StreamFactory $stream_factory)
	{
		$this->jin           = $jin;
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
			$matchers = array();
			$uri_path = $request->getUri()->getPath();
			$is_dir   = substr($uri_path, -1, 1) == '/';

			if ($is_dir) {
				$path = '@pages' . $uri_path . 'index.html';
				$alt  = '@pages' . substr($uri_path, 0, -1) . '.html';

			} else {
				$path = '@pages' . $uri_path . '.html';
				$alt  = '@pages' . $uri_path . '/index.html';
			}

			if ($request->getHeaderLine('X-Requested-With') == 'xmlhttprequest') {
				$path = str_replace('/' . basename($path), '/%' . basename($path), $path);
				$alt  = str_replace('/' . basename($alt),  '/%' . basename($alt),  $alt);
			} else {
				$path = str_replace('/' . basename($path), '/@' . basename($path), $path);
				$alt  = str_replace('/' . basename($alt),  '/@' . basename($alt),  $alt);
			}

			if ($is_dir) {
				$matcher_config = $path . '~matchers.jin';
			} else {
				$matcher_config = dirname($path) . '/' . '~matchers.jin';
			}

			if ($this->manager->has($matcher_config)) {
				$matchers = $this->jin->parse($this->manager->load($matcher_config)->render());
				$endpoint = basename($uri_path);
				$matches  = array();
			}

			if (!$matchers || !isset($matchers[$endpoint])) {
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

				} else {
					foreach ($matchers as $template => $matcher) {
						if (!preg_match('#' . $matcher['pattern'] . '#', $endpoint, $matches)) {
							continue;
						}

						array_shift($matches);

						if (count($matches) != count($matcher['mapping'])) {
							throw new RuntimeException(sprintf(
								''
							));
						}

						$matches  = array_combine($matcher['mapping'], $matches);

						if ($request->getHeaderLine('HX-Request')) {
							$template = '%' . $template . '.html';
						} else {
							$template = '@' . $template . '.html';
						}

						$response = $response
							->withStatus(200)
							->withHeader('Content-Type', 'text/html; charset=utf-8')
							->withBody($this->streamFactory->createStream(
								$this->manager->load(
									'@pages' . dirname($uri_path) . '/' . $template,
									[
										'request'    => $request,
										'parameters' => $matches
									]
								)->render()
							))
						;
					}
				}
			}
		}

		return $response;
	}
}
