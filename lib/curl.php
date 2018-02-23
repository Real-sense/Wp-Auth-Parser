<?php

class Curl {
	private $ch;
	private $host;
	private $options;

	public static function app ($host) {
		return new self($host);
	}

	private function __construct ($host) {
		$this->ch = curl_init();
		$this->host = $host;

		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	}

	public function __destruct () {
		curl_close($this->ch);
	}

	public function set ($name, $value) {
		$this->options[$name] = $value;
		curl_setopt($this->ch, $name, $value);

		return $this;
	}

	public function get ($name) {
		return $this->options[$name];
	}

	public function ssl ($act) {
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $act);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $act);

		return $this;
	}

	public function headers ($act) {
		$this->set(CURLOPT_HEADER, $act);

		return $this;
	}

	public function follow ($param) {
		$this->set(CURLOPT_FOLLOWLOCATION, $param);

		return $this;
	}

	public function referer ($url) {
		$this->set(CURLOPT_REFERER, $url);

		return $this;
	}

	public function agent ($agent) {
		$this->set(CURLOPT_USERAGENT, $agent);

		return $this;
	}

	public function add_header ($header) {
		$this->options[CURLOPT_HTTPHEADER][] = $header;
		$this->set(CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER]);
		return $this;
	}

	public function add_headers ($headers) {
		foreach ($headers as $h) {
			$this->options[CURLOPT_HTTPHEADER][] = $h;
		}

		$this->set(CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER]);

		return $this;
	}

	public function clear_headers () {
		$this->options[CURLOPT_HTTPHEADER] = array();
		$this->set(CURLOPT_HTTPHEADER, $this->options[CURLOPT_HTTPHEADER]);

		return $this;
	}

	public function config_load ($file) {
		$data = file_get_contents($file);
		$data = unserialize($data);

		curl_setopt_array($this->ch, $data);

		foreach ($data as $key => $val) {
			$this->options[$key] = $val;
		}

		return $this;
	}

	public function config_save ($file) {
		$data = serialize($this->options);

		file_put_contents($file, $data);

		return $this;
	}

	public function request ($url) {
		curl_setopt($this->ch, CURLOPT_URL, $this->make_url($url));
		$data = curl_exec($this->ch);

		return $this->process_result($data);
	}

	private function make_url ($url) {
		if ($url[0] != '/')
			$url = '/' . $url;

		return $this->host . $url;
	}

	private function process_result ($data) {
		if ( !isset($this->options[CURLOPT_HEADER]) || !$this->options[CURLOPT_HEADER] ) {
			return array(
				'headers' => array(),
				'html' => $data
			);
		}

		$info = curl_getinfo($this->ch);

		$headers_part = trim( substr($data, 0, $info['header_size']) );
		$body_part = substr($data, $info['header_size']);

		$headers_part = str_replace("\r\n", "\n", $headers_part);
		$headers = str_replace("\r", "\n", $headers_part);

		$headers = explode("\n\n", $headers);
		$headers_part = end($headers);

		$lines = explode("\n", $headers_part);
		$headers = array();

		$headers['start'] = $lines[0];

		for ($i = 1; $i < count($lines); $i++) {
			$del_pos = strpos($lines[$i], ':');
			$name = substr($lines[$i], 0, $del_pos);
			$value = substr($lines[$i], $del_pos + 2);
			$headers[$name] = $value;
		}

		return array(
			'headers' => $headers,
			'html' => $body_part
		);
	}

	public function cookie ($path) {
		$this->set(CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/'.$path);
		$this->set(CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/'.$path);

		return $this;
	}

	public function post ($data) {
		if ($data === false) {
			$this->set(CURLOPT_POST, false);
			return $this;
		}

		$this->set(CURLOPT_POST, true);
		$this->set(CURLOPT_POSTFIELDS, http_build_query($data));

		return $this;
	}
}