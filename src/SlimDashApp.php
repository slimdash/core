<?php
namespace SlimDash\Core;

/**
 * This is the SlimDash App
 */
class SlimDashApp extends \Slim\App {
	/**
	 * @var array
	 */
	protected $moduleInstances = [];

	/**
	 * Initialize a new instance of ModularApp
	 * @param array $settings
	 */
	public function __construct($settings = array()) {
		$modules = $this->moduleInstances;

		// loop through modules directory
		$myModules = $this->getAvailableModules($settings);
		foreach ($myModules as $moduleName => $moduleClassName) {
			$module = new $moduleClassName();

			if (!$module instanceof SlimDashModule) {
				throw new \Exception($moduleName . ' is not an instance of SlimDashModule');
			}

			$this->moduleInstances[] = $module;
		}

		// sort it
		usort($this->moduleInstances, function ($a, $b) {
			return $a->getPriority() - $b->getPriority();
		});

		// push in app module first
		$appModuleName = $settings['settings']['appmodule'];
		array_unshift($this->moduleInstances, new $appModuleName());

		// load module settings
		$allSettings = array_merge_recursive($settings, []);
		foreach ($this->moduleInstances as $module) {
			$allSettings = array_merge_recursive($allSettings, $module->getSettings());
		}

		// finally, call the base constructor with all settings
		parent::__construct($allSettings);
	}

	public function getAvailableModules($settings) {
		$modulesDir = $settings['settings']['modules_dir'];
		$modules = [];
		foreach (glob($modulesDir . '*', GLOB_ONLYDIR) as $dir) {
			$modules[] = basename($dir) . '\\Module';
		}

		return $modules;
	}

	/**
	 * Initialize all modules.
	 * @return void
	 */
	public function initModules() {
		$moduleInstances = $this->moduleInstances;

		// init dependencies
		foreach ($moduleInstances as $module) {
			$module->initDependencies($this);
		}

		// init middlewares
		foreach ($moduleInstances as $module) {
			$module->initMiddlewares($this);
		}

		// init routes
		foreach ($moduleInstances as $module) {
			$module->initRoutes($this);
		}
	}

	/**
	 * Customized route mapping.
	 */
	public function route(array $methods, $uri, $controller, $func = null) {
		if ($func) {
			return $this->map($methods, $uri, function ($request, $response, $args) use ($controller, $func) {
				$callable = new $controller($request, $response, $args, $this);
				return call_user_func_array([$callable, $request->getMethod() . ucfirst($func)], $args);
			});
		}
		return $this->map($methods, $uri, function ($request, $response, $args) use ($controller) {
			$callable = new $controller($request, $response, $args, $this);
			return call_user_func_array([$callable, $request->getMethod()], $args);
		});
	}
}