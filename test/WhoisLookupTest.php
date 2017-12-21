<?php

use Whois\WhoisLookup;

class WhoisLookupTest extends PHPUnit_Framework_TestCase
{
	public function testLookup()
	{
		$sld = 'jeng.cc';

		$domain = new WhoisLookup($sld);
		$whois_answer = $domain->info();

		echo $whois_answer;
	}

	public function testIfNicIsAvailable()
	{

	}

	public function testDomainIsAvailable()
	{
		$domain = new WhoisLookup('sadlasjdsalkdajdasl.ch');
		$this->assertTrue($domain->isAvailable());
	}

	public function testDomainIsNotAvailable()
	{
		$domain = new WhoisLookup('jeng.cc');
		$this->assertTrue($domain->isAvailable());
	}
}