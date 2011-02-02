<?php
/**
 * Response Class
 *
 * @author lzyy http://blog.leezhong.com
 * @version 0.1.0
 */
class Response extends Witty_Base {

	public static $messages = array(
		100 => 'Continue',
		101 => 'Switching Protocols',

		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',

		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	);

	protected $_status = 200;

	protected $_headers = array();

	protected $_body = NULL;

	protected $_cookies = array();

	protected function _after_config()
	{
		$this->_headers += array('Content-Type' => 'text/html; charset=utf-8');
	}

	public function __toString()
	{
		return (string) $this->_body;
	}

	public function set_body($content)
	{
		$this->_body = $content;
	}

	public function set_status($status)
	{
		if (array_key_exists($status, Response::$messages))
		{
			$this->_status = (int) $status;
			return $this;
		}
		else
		{
			throw new Response_Exception('unkonw status code: {code}', array('{code}' => $status));
		}
	}

	public function get_status()
	{
		return $this->_status;
	}

	public function add_headers(array $arr)
	{
		$this->_headers += $arr;
		return $this;
	}

	public function set_headers(array $arr)
	{
		$this->_headers = array_merge($this->_headers, $arr);
	}

	public function get_headers()
	{
		return $this->_headers;
	}

	public function send_headers()
	{
		if (!headers_sent())
		{
			if (isset($_SERVER['SERVER_PROTOCOL']))
				$protocol = $_SERVER['SERVER_PROTOCOL'];
			else
				$protocol = 'HTTP/1.1';

			header($protocol.' '.$this->_status.' '.Response::$messages[$this->_status]);

			foreach ($this->_headers as $name => $value)
			{
				header($name.':'.$value, true);
			}
		}
		return $this;
	}

	public function generate_etag()
	{
		if ($this->_body === NULL)
		{
			throw new Response_Exception('no result, etag generate failed');
		}
		return '"'.sha1($this->_body).'"';
	}

	public function check_etag($etag = NULL)
	{
		if (empty($etag))
		{
			$etag = $this->generate_etag();
		}

		$this->_headers['ETag'] = $etag;
		$this->_headers += array(
			'Cache-Control' => 'must-revalidate',
		);

		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)
		{
			$this->_status = 304;
			$this->send_headers();
			exit;
		}

		return $this;
	}
}
