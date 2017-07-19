<?php

namespace BePark\Libs\Laravel\RouteController\Routing;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str;

/**
 * Inspired by two other stuff
 *
 * @see https://github.com/laravel/framework/blob/5.2/src/Illuminate/Routing/ControllerInspector.php
 * @see https://github.com/laravel/framework/blob/5.2/src/Illuminate/Routing/Router.php#L244
 * @see https://gist.github.com/cotcotquedec/df15f9111e5c8d118ac270f6a157c460
 */
class ControllerRouter
{
	/**
	 * An array of HTTP verbs.
	 *
	 * @var array
	 */
	protected $_verbs = [
		'any',
		'get',
		'post',
		'put',
		'patch',
		'delete',
		'head',
		'options',
	];

	/**
	 * The name of index action
	 *
	 * @var string
	 */
	protected $_indexAction = 'index';

	/**
	 * Get a full routable list from the controller based on method name
	 *
	 * @param string $controllerClass
	 * @param string|null $prefix
	 * @return array
	 */
	public function listRoutableActionFromController(string $controllerClass, string $prefix = null): array
	{
		$reflection = new ReflectionClass($controllerClass);
		$controllerName = Str::slug(Str::replaceLast('Controller', '', $reflection->getShortName()));

		$prefix = (empty($prefix) ? '' : ($prefix . '.')) . $controllerName;

		// only public method that start with specific keyworkds will be loaded
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

		$routable = array();
		foreach ($methods as $method)
		{
			$parts = $this->methodToRouteParts($method);
			if ($this->isRoutable($method, $parts['verb']))
			{
				$isIndex = $parts['action'] == $this->_indexAction;

				// build uri parts
				$uri = ($isIndex ? '' : '/' . $parts['action']);

				// manage parameters
				foreach ($method->getParameters() as $parameter)
				{
					$uri .= sprintf('/{%s%s}', $parameter->getName(), $parameter->isDefaultValueAvailable() ? '?' : '');
				}

				$routable[ sprintf('%s@%s', '\\' . $controllerClass, $parts['full']) ] = $parts + [
						'name' => sprintf('%s.%s.%s', $prefix, $parts['action'], $parts['verb']),
						'uri' => $uri,
					];
			}
		}

		return $routable;
	}

	/**
	 * Determine if the given controller method is routable.
	 *
	 * @param  \ReflectionMethod  $method
	 * @param string $verb get, post, any, etc
	 * @return bool
	 */
	public function isRoutable(ReflectionMethod $method, string $verb): bool
	{
		if ($method->class == 'Illuminate\Routing\Controller')
		{
			return false;
		}

		return in_array($verb, $this->_verbs);
	}

	/**
	 * Split the method names into multi-parts
	 *
	 * @param ReflectionMethod $method
	 * @return array verb, action
	 */
	public function methodToRouteParts(ReflectionMethod $method): array
	{
		$name = $method->getName();
		$parts = explode('_', Str::snake($name));
		return [
			'full' => $name,
			'verb' => $parts[0],
			'action' => Str::slug(implode('-', array_slice($parts, 1))),
		];
	}
}
