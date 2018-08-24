<?php
namespace Request;

/**
 * Class Resource
 */
class Resource {
	/**
	 * @var mixed
	 */
	private $data;
	/**
	 * @var string
	 */
	private $fragment;
	/**
	 * @var string[]
	 */
	private $headers = [];
	/**
	 * @var string
	 */
	private $hostname;
	/**
	 * @var array
	 */
	private $query = [];
	/**
	 * @var string
	 */
	private $password;
	/**
	 * @var string
	 */
	private $path;
	/**
	 * @var int
	 */
	private $port;
	/**
	 * @var bool
	 */
	private $secure;
	/**
	 * @var string (e.g. ftp, http, https)
	 */
	private $scheme;
	/**
	 * @var string
	 */
	private $username;

	/**
	 * Assigns this object to the current loaded resource (i.e. Server request)
	 */
	public function setCurrent() {
		// Resource
		if (isset($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) {
			$this->setScheme('https');
		} else {
			$this->setScheme('http');
		}
		$this->setHostname($_SERVER['HTTP_HOST']);
		$this->setPort($_SERVER['SERVER_PORT']);
		$this->setPath($_SERVER['REQUEST_URI']);
		$this->setQuery($_SERVER['QUERY_STRING']);

		// Credentials
		$this->setPassword($_SERVER['PHP_AUTH_PW'] ?? null);
		$this->setUsername($_SERVER['PHP_AUTH_USER'] ?? null);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$fragment = $this->getFragment();
		$port = $this->getPort();
		$password = $this->getPassword();
		$query = $this->getQuery();
		$scheme = $this->getScheme();
		$username = $this->getUsername();

		$schemePorts = self::getSchemePorts();
		$schemePort = $schemePorts[$scheme] ?? 0;

		$resource = '';
		$resource .= $scheme . '://';
		if ($username) {
			if ($password) {
				$resource .= $username . ':' . $password;
			} else {
				$resource .= $username;
			}
			$resource .= '@';
		}
		$resource .= $this->getHostname();
		if ($port && (!$schemePort || $schemePort !== $port)) {
			$resource .= ':' . $port;
		}
		$resource[] = $this->getPath();
		if ($query) {
			$resource .= '?' . $query;
		}
		if ($fragment) {
			$resource .= '#' . $fragment;
		}

		return (string) $resource;
	}

	/**
	 * @return mixed POST data
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * @return string
	 */
	public function getHostname() {
		return $this->hostname;
	}

	/**
	 * @return array
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	/**
	 * @return array
	 */
	public static function getSchemePorts() {
		return [
			'ftp' => 22,
			'http' => 80,
			'https' => 443,
		];
	}

	/**
	 * @return bool
	 */
	public function getSecure() {
		return $this->secure;
	}

	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @param mixed $data
	 */
	public function setData($data) {
		$this->data = $data;
	}

	/**
	 * @param string $fragment
	 */
	public function setFragment($fragment) {
		$this->fragment = \Input::validate($fragment);
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @throws Exception
	 */
	public function setHeader($key, $value) {
		$value = \Input::validate($value);

		if ($key === null) {
			$this->headers[] = $value;
		} else {
			$key = \Input::validate($key);
			$key = strtolower($key);

			$this->headers[$key] = $value;
		}
	}

	/**
	 * @param string $hostname
	 */
	public function setHostname($hostname) {
		$this->hostname = \Input::validate($hostname);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param bool $append
	 */
	public function setQueryParameter($name, $value, $append=false) {
		$name = \Input::validate($name);
		$value = \Input::validate($value);

		if (array_key_exists($name, $this->query) && $append) {
			if (!is_array($this->query)) {
				$this->query[$name] = [];
			}

			$this->query[$name][] = $value;
		} else {
			$this->query[$name] = $value;
		}
	}

	/**
	 * @param string $raw
	 */
	public function setQuery($raw) {
		parse_str($raw, $query);

		$this->query = [];
		foreach ($query as $name => $value) {
			$this->setQueryParameter($name, $value, true);
		}
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = \Input::validate($password);
	}

	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = \Input::validate($path);
	}

	/**
	 * @param string $port
	 */
	public function setPort($port) {
		$this->port = \Input::validate($port, 'int');
	}

	/**
	 * @param string $scheme
	 */
	public function setScheme($scheme) {
		$this->scheme = \Input::validate($scheme);
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = \Input::validate($username);
	}
}
