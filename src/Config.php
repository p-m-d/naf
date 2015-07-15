<?php

namespace Naf;

class Config {

	protected static $_config = [];

	public static function set($key = null, $value = null) {
		return static::config($key, $value);
	}

	public static function get($key = null) {
		return static::config($key);
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
}