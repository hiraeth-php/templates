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
	 * The URL Generator
	 *
	 * @var Http\UrlGenerator|null
	 */
	protected $urlGenerator = NULL;


	/**
	 * Check whether or not a request is traditional AJAX
	 */
	static public function isAjax(Request $request): bool
	{
		return strtolower($request->getHeaderLine('X-Requested-With')) == 'xmlhttprequest';
	}


	/**
	 * Check whether or not a request is asynchronous
	 */
	static public function isAsync(Request $request): bool
	{
		return static::isHTMX($request)
			|| static::isAjax($request)
		;
	}


	/**
	 * Check whether or not a request is from HTMX
	 */
	static public function isHTMX(Request $request): bool
	{
		return $request->getHeaderLine('HX-Request')
			&& !$request->getHeaderLine('HX-Boosted');
	}


	/**
	 * Create a new instance of the middleware
	 */
	public function __construct(Jin\Parser $jin, Manager $manager, StreamFactory $stream_factory, Http\UrlGenerator $url_generator)
	{
		$this->jin           = $jin;
		$this->manager       = $manager;
		$this->streamFactory = $stream_factory;
		$this->urlGenerator  = $url_generator;
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

		$parameters = [];
		$consumed   = NULL;
		$template   = '@pages';
		$uri_path   = $request->getUri()->getPath();
		$segments   = explode('/', trim((string) $uri_path, '/'));
		$is_dir     = str_ends_with((string) $uri_path, '/');

		if (static::isAsync($request)) {
			$type = '%';
		} else {
			$type = '@';
		}

		while (count($segments)) {
			$segment = urldecode(array_shift($segments));
			$config  = $template . '/~matchers.jin';

			if ($segment[0] == '_') {
				return $response;
			}

			if ($this->manager->has($config)) {
				$matchers = $this->jin->parse($this->manager->load($config)->render([
					'request'    => $request,
					'response'   => $response,
					'parameters' => $parameters
				]));

				foreach ($matchers as $branch => $matcher) {
					if (!in_array($type, $matcher['include'] ?? ['%', '@'])) {
						continue;
					}

					if (!preg_match('#' . $matcher['pattern'] . '#', (string) $segment, $matches)) {
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
						$is_dir   = FALSE;
						$consumed = '/' . implode("/", $segments) . ($is_dir ? '/' : '');
						$segments = [];
					}

					break;
				}
			}

			$template .= '/' . rtrim($segment, '/');
		}

		foreach ($parameters as $name => $value) {
			$template = str_replace('&' . $name, $value, $template);
		}

		if ($is_dir) {
			$try_template = $template . '/' . $type . 'index.html';
			$alt_template = dirname($template) . '/' . $type . basename($template) . '.html';
		} else {
			$alt_template = $template . '/' . $type . 'index.html';
			$try_template = dirname($template) . '/' . $type . basename($template) . '.html';
		}

		if ($this->manager->has($try_template)) {
			try {
				$template = $this->manager->load($try_template, [
					'parameters' => $parameters,
					'response'   => $response,
					'request'    => $request
						->withAttribute('_async_', static::isAsync($request))
						->withAttribute('_consumed_', $consumed)
				]);

				return $response
					->withBody($this->streamFactory->createStream(
						$template->set('this', $template)->render())
					)
					->withHeader('Content-Type', 'text/html; charset=utf-8')
					->withStatus($template->get('_status_') ?? 200)
				;

			} catch (Exception $e) {
				//
				// Templates will throw exception on render, we want to make sure they're not
				// masking our Interrupt request
				//

				for ($current = $e; $current; $current = $current->getPrevious()) {
					if ($current instanceof Http\Interrupt) {
						return $current->getResponse();
					}
				}

				throw $e;
			}
		}

		if ($this->manager->has($alt_template)) {
			$response = $response->withStatus(301);
			$location = $request->getUri()->withPath(
				$this->urlGenerator->call(
					$is_dir
						? substr((string) $uri_path, 0, -1)
						: $uri_path . '/'
				)
			);

			if (static::isHTMX($request)) {
				return $response
					->withStatus(205)
					->withHeader('HX-Redirect', $location->getPath())
				;
			}

			return $response->withHeader('Location', (string) $location);
		}

		return $response;
	}
}
