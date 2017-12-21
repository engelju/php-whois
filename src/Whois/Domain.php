<?php

namespace Whois;

class Domain
{
	private $fullDomain;
	private $TLDs;
	private $subDomain;

	public function __construct($domain)
	{
		$this->fullDomain = strtolower($domain);

		// check $domain syntax and split full domain name on subdomain and TLDs
		if (preg_match('/^([\p{L}\d\-]+)\.((?:[\p{L}\-]+\.?)+)$/ui', $this->fullDomain, $matches) ||
			preg_match('/^(xn\-\-[\p{L}\d\-]+)\.(xn\-\-(?:[a-z\d-]+\.?1?)+)$/ui', $this->fullDomain, $matches)
		) {
			$this->subDomain = $matches[1];
			$this->TLDs = $matches[2];
		} else throw new \InvalidArgumentException("Invalid $this->fullDomain syntax");

		if (!$this->isValid())
			throw new \InvalidArgumentException("Domain name isn't valid!");

		return $this;
	}

	public function isValid()
	{
		$regex_containsAtLeast3Chars = "/^[a-z0-9\-]{3,}$/";
		$regex_startOrEndWithDash = "/^-|-$/";

		if (preg_match($regex_containsAtLeast3Chars, $this->subDomain) &&
			!preg_match($regex_startOrEndWithDash, $this->subDomain))
		{
			return true;
		}

		return false;
	}

	/**
	 * @return string full domain name
	 */
	public function getFullDomain()
	{
		return $this->fullDomain;
	}

	/**
	 * @return string top level domains separated by dot
	 */
	public function getTLDs()
	{
		return $this->TLDs;
	}

	/**
	 * @return string return subdomain (low level domain)
	 */
	public function getSubDomain()
	{
		return $this->subDomain;
	}

}