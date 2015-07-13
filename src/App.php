<?php

namespace Naf;

use Infiltrate\FilterableStaticTrait;

class App {

	use FilterableStaticTrait;

	public static $classPaths = [
		'controller' => ['Controller', 'Action'],
		'view' => ['View', 'Action'],
		'console' => ['Console', 'Command']
	];

	protected static $_plugins = ['Naf\\Action', 'Naf\\Console'];

	protected static $_config = [
		'debug' => false,
		'app' => [
			'namespace' => 'App'
		],
		'error' => []
	];

	public static function startup() {
		//@todo filter
		register_shutdown_function([get_called_class(), 'shutdown']);
	}

	public static function shutdown() {
		//@todo filter
	}

	public static function config($key = null, $value = null) {
		if (is_null($key) && is_null($value)) {
			return static::$_config;
		}
		$path = explode('.', $key);
		if (!is_null($value)) {
			if (is_null($key)) {
				static::$_config = (array)$value;
			} else {
				$config =& static::$_config;
				foreach ($path as $i => $k) {
					if (is_numeric($k) && intval($k) > 0 || $k === '0') {
						$k = intval($k);
					}
					if ($i === count($path) - 1) {
						$config[$k] = $value;
					} else {
						if (!isset($config[$k])) {
							$config[$k] = [];
						}
						$config =& $config[$k];
					}
				}
			}
			return static::config($key);
		} else {
			$value = null;
			$config = static::$_config;
			while ($index = array_shift($path)) {
				if (isset($config[$index])) {
					$config = $config[$index];
					if (empty($path)) {
						$value = $config;
					}
				} else {
					break;
				}
			}
			return $value;
		}
	}

	public static function merge(array $data, $merge) {
		$args = func_get_args();
		$return = current($args);
		while (($arg = next($args)) !== false) {
			foreach ((array)$arg as $key => $val) {
				if (!empty($return[$key]) && is_array($return[$key]) && is_array($val)) {
					$return[$key] = static::merge($return[$key], $val);
				} elseif (is_int($key) && isset($return[$key])) {
					$return[] = $val;
				} else {
					$return[$key] = $val;
				}
			}
		}
		return $return;
	}

	public static function locate($name, $type = null) {
		//@todo needs lots of love
		if (strpos($name, '\\') && class_exists($name)) {
			return $name;
		}
		$params = compact('name', 'type');
		return static::_filter(__FUNCTION__, $params, function($self, $params){
			extract($params);
			if (isset($self::$classPaths[$type])) {
				foreach ($self::$classPaths[$type] as $path) {
					$app = $self::config('app.namespace');
					$namespaces = array_merge([$app], $self::$_plugins, ['Naf']);
					foreach ($namespaces as $namespace) {
						$classPath = "{$namespace}\\{$path}\\{$name}";
						if (class_exists($classPath)) {
							return $classPath;
						}
					}
				}
			}
		});
	}

	public static function plugin() {}
}

?>