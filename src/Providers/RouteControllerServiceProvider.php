<?php

namespace BePark\Libs\Laravel\RouteController\Provider;

use Illuminate\Support\ServiceProvider;

class RouteControllerServiceProvider extends ServiceProvider
{
	public function boot()
	{
		Route::macro(
			'controller',
			function($uri, $controller)
			{
				$fullControllerName = $controller;

				// first we check namespace and prefix for the roads
				// get prefix on route by group
				if (!empty($this->groupStack))
				{
					$group = end($this->groupStack);
					$fullControllerName = isset($group[ 'namespace' ]) && strpos($controller, '\\') !== 0 ? $group[ 'namespace' ] . '\\' . $controller : $controller;
					$prefix = str_slug($group[ 'prefix' ] ?? '');
				}

				$cr = new ControllerRouter();
				$routable = $cr->listRoutableActionFromController($fullControllerName);

				foreach ($routable as $uses => $potentialRoute)
				{
					$action = ['uses' => $uses];

					$action[ 'as' ] = $potentialRoute[ 'name' ];
					$this->{$potentialRoute[ 'verb' ]}($uri . $potentialRoute[ 'uri' ], $action);
				}
			}
		);
	}
}
