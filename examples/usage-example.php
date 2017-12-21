<?php

include '../vendor/autoload.php';

$sld = 'reg.ru';

$domain = new Whois\WhoisLookup($sld);

$whois_answer = $domain->info();

echo $whois_answer;

if ($domain->isAvailable()) {
    echo "Domain is available\n";
} else {
    echo "Domain is registered\n";
}
