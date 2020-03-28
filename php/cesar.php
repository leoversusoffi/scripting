<?php

$s = "9P&;gFD,5.BOPCdBl7Q+@V'1dDK?qL";


for($i=0;$i<=128;$i++) {
$s = str_split($s);
$n = '';
foreach ($s as $c) {
	$o = ord($c);
	$o++;
	$o = $o%128;
	$c = chr($o);
	$n .= $c;
}

echo $n."\n";
$s = $n;
}

