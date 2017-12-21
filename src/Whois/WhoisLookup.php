<?php

namespace Whois;

class WhoisLookup
{
	private $domain;
	private $servers;
	private $nicServer = '';
	private $info = null;

	/**
	 * @param string $domain full domain name (without trailing dot)
	 */
	public function __construct($domain)
	{
		$this->domain = new Domain($domain);
		$this->servers = json_decode(file_get_contents(__DIR__ . '/whois.servers.json'), true);

		$whois_server = $this->getNicServer();
		if (!$whois_server)
			return "No whois server for this tld in list!";
	}

	private function getNicServer()
	{
		if ($this->nicServer) {
			return $this->nicServer;
		}

		if (isset($this->servers[$this->domain->getTLDs()][0])) {
			$this->nicServer = $this->servers[$this->domain->getTLDs()][0];
			return $this->nicServer;
		}

		return false;
	}

	public function info()
	{
		// if whois server serve replay over HTTP protocol instead of WHOIS protocol
		if (preg_match("/^https?:\/\//i", $this->nicServer)) {
			$string = $this->makeCurlRequest();
		} else {
			$string = $this->makeWhoisRequest();
		}

		$string_encoding = mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true);
		$string_utf8 = mb_convert_encoding($string, "UTF-8", $string_encoding);

		return htmlspecialchars($string_utf8, ENT_COMPAT, "UTF-8", true);
	}

	private function makeCurlRequest()
	{
		$url = $this->nicServer.$this->domain->getSubDomain().'.'.$this->domain->getTLDs();

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$data = curl_exec($ch);

		if (curl_error($ch)) {
			return "Connection error!";
		} else {
			$string = strip_tags($data);
		}
		curl_close($ch);

		return $string;
	}

	private function makeWhoisRequest()
	{
		$fp = fsockopen($this->nicServer, 43);
		if (!$fp) {
			return "Connection error!";
		}

		$dom = $this->domain->getSubDomain() . '.' . $this->domain->getTLDs();
		fputs($fp, "$dom\r\n");

		// Getting string
		$string = '';

		// Checking whois server for .com and .net
		if ($this->domain->getTLDs() == 'com' || $this->domain->getTLDs() == 'net') {

			while (!feof($fp)) {
				$line = trim(fgets($fp, 128));
				$string .= $line;
				$lineArr = explode (":", $line);
				if (strtolower($lineArr[0]) == 'whois server') {
					$this->nicServer = trim($lineArr[1]);
				}
			}

			// Getting whois information
			$fp = fsockopen($this->nicServer, 43);
			if (!$fp) {
				return "Connection error!";
			}

			$dom = $this->domain->getSubDomain() . '.' . $this->domain->getTLDs();
			fputs($fp, "$dom\r\n");

			// Getting string
			$string = '';

			while (!feof($fp)) {
				$string .= fgets($fp, 128);
			}
		} else {
			// Checking for other tld's
			while (!feof($fp)) {
				$string .= fgets($fp, 128);
			}
		}

		fclose($fp);

		return $string;
	}

	public function isAvailable()
	{
		// todo: chache info
		$whois_string = $this->info();

		// todo: cache not_found_string
		$not_found_string = '';
		if (isset($this->servers[$this->domain->getTLDs()][1])) {
			$not_found_string = $this->servers[$this->domain->getTLDs()][1];
		}

		$whois_string2 = @preg_replace('/' . $this->domain->getFullDomain() . '/', '', $whois_string);
		$whois_string = @preg_replace("/\s+/", ' ', $whois_string);

		$array = explode (":", $not_found_string);
		if ($array[0] == "MAXCHARS") {
			if (strlen($whois_string2) <= $array[1]) {
				return true;
			}
			return false;
		} else {
			if (preg_match("/" . $not_found_string . "/i", $whois_string)) {
				return true;
			}
			return false;
		}
	}

	public function htmlInfo()
	{
		return nl2br($this->info());
	}
}