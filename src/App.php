<?php

namespace Naf;

use Infiltrate\FilterableStaticTrait;

class App {

	use FilterableStaticTrait;

	public static $classPaths = [];

	protected static $namespaces = [];

	/**
	 *
	 * @param array $config
	 */
	public static function bootstrap(array $config = []) {
		$defaults = [
			'debug' => false,
			'app' => [
				'namespace' => 'App'
			],
			'error' => []
		];
		$params = compact('defaults', 'config');
		return static::filterStaticMethod(__FUNCTION__, $params, function($self, $params){
			extract($params);
			$config = Config::merge($defaults, $config);
			Config::set(null, $config);
			$self::provide(Config::get('App.namespace'));
		});
	}

	/**
	 *
	 */
	public static function startup() {
		return static::filterStaticMethod(__FUNCTION__, [], function($self){
			register_shutdown_function([get_called_class(), 'shutdown']);
		});
	}

	/**
	 *
	 */
	public static function shutdown() {
		return static::filterStaticMethod(__FUNCTION__, [], function(){});
	}

	/**
	 * @todo needs lots of love
	 *
	 * @param string $name
	 * @param string $type
	 * @throws \Exception
	 * @return string
	 */
	public static function locate($name, $type = null) {
		if (strpos($name, '\\') && class_exists($name)) {
			return $name;
		}
		$type = $type ? strtolower($type) : $type;
		$params = compact('name', 'type');
		return static::filterStaticMethod(__FUNCTION__, $params, function($self, $params){
			extract($params);
			if (isset($type, $self::$classPaths[$type])) {
				foreach ($self::$classPaths[$type] as $path) {
					$app = Config::get('app.namespace');
					$namespaces = array_merge([$app], $self::$namespaces, [__NAMESPACE__]);
					foreach ($namespaces as $namespace) {
						$classPath = "{$namespace}\\{$path}\\{$name}";
						if (class_exists($classPath)) {
							return $classPath;
						}
					}
				}
			}
		});
		$message = sprintf("Class '%s' of type '%s' not found.", $name, $type);
		throw new \Exception($message);
	}

	/**
	 * @todo  needs lots of love
	 *
	 * @param string $namespace
	 * @param array $classPaths
	 */
	public static function provide($namespace = null, $classPaths = []) {
		if ($namespace && !in_array($namespace, static::$namespaces)) {
			static::$namespaces[] = $namespace;
		}
		foreach ($classPaths as $type => $classPaths) {
			if (!isset(static::$classPaths[$type])) {
				static::$classPaths[$type] = [];
			}
			foreach ((array) $classPaths as $classPath) {
				if (!in_array($classPath, static::$classPaths[$type])) {
					static::$classPaths[$type][] = $classPath;
				}
			}
		}
	}
}

?>