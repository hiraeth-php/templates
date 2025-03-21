<?php

namespace Hiraeth\Templates;

use Dotink\Jin;
use Hiraeth\Http;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use RuntimeException;
use Exception;

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
	 * Check whether or not a request is asynchronous
	 */
	static public function isAsync(Request $request): bool
	{
		return strtolower($request->getHeaderLine('X-Requested-With')) == 'xmlhttprequest'
			|| $request->getHeaderLine('HX-Request')
		;
	}


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

		if ($response->getStatusCode() != 404) {
			return $response;
		}

		$parameters = array();
		$template   = '@pages';
		$uri_path   = $request->getUri()->getPath();
		$segments   = explode('/', trim($uri_path, '/'));
		$is_dir     = substr($uri_path, -1, 1) == '/';

		if (static::isAsync($request)) {
			$type = '%';
		} else {
			$type = '@';
		}

		while (count($segments)) {
			$segment = array_shift($segments);
			$config  = $template . '/~matchers.jin';

			if ($this->manager->has($config)) {
				$matchers = $this->jin->parse($this->manager->load($config)->render([
					'request'    => $request,
					'parameters' => $parameters
				]));

				foreach ($matchers as $branch => $matcher) {
					if (!in_array($type, $matcher['include'] ?? ['%', '@'])) {
						continue;
					}

					if (!preg_match('#' . $matcher['pattern'] . '#', $segment, $matches)) {
						continue;
					}

					array_shift($matches);

					if (count($matches) != count($matcher['mapping'] ?? [])) {
						throw new RuntimeException(sprintf(
							'Number of matches does not match number of mapped parameters'
						));
					}

					$segment    = $branch;
					$parameters = array_merge(
						$parameters,
						array_combine($matcher['mapping'] ?? [], $matches)
					);

					if ($matcher['consume'] ?? FALSE) {
						$is_dir   = substr($segment, -1, 1) == '/';
						$segments = [];
					}
				}
			}

			$template .= '/' . $segment;
		}

		$template = rtrim($template, '/');

		if ($is_dir) {
			$try_template = $template . '/' . $type . 'index.html';
			$alt_template = dirname($template) . '/' . $type . basename($template) . '.html';
		} else {
			$alt_template = $template . '/' . $type . 'index.html';
			$try_template = dirname($template) . '/' . $type . basename($template) . '.html';
		}

		if ($this->manager->has($try_template)) {
			try {
				return $response
					->withStatus(200)
					->withHeader('Content-Type', 'text/html; charset=utf-8')
					->withBody($this->streamFactory->createStream(
						$this->manager->load(
							$try_template,
							[
								'request'    => $request,
								'parameters' => $parameters
							]
						)->render()
					))
				;

			} catch (Exception $e) {
				$current = $e;

				while (!$current instanceof Http\Interrupt) {
					$current = $current->getPrevious();

					if (!$current) {
						throw $e;
					}
				}

				return $current->getResponse();
			}
		}

		if ($this->manager->has($alt_template)) {
			$response = $response->withStatus(301);

			if ($is_dir) {
				return $response->withHeader(
					'Location',
					(string) $request->getUri()->withPath(substr($uri_path, 0, -1))
				);

			} else {
				return $response->withHeader(
					'Location',
					(string) $request->getUri()->withPath($uri_path . '/')
				);
			}
		}

		return $response;
	}
}
