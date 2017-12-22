<?php

use Whois\WhoisLookup;
use PHPUnit\Framework\TestCase;

class WhoisLookupTest extends TestCase
{
	public function testWhoisAnswer()
	{
		$domain = new WhoisLookup('jeng.cc');
		$this->assertTrue($domain->getWhois() != '');
	}

	public function testDomainIsAvailable()
	{
		$domain = new WhoisLookup('sadlasjdsalkdajdasl.ch');
		$this->assertTrue($domain->isAvailable());
	}

	public function testDomainIsNotAvailable()
	{
		$domain = new WhoisLookup('jeng.cc');
		$this->assertFalse($domain->isAvailable());
	}
}