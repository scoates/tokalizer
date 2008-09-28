<?php

require '../include/TokenSet.class.php';

$ti = new TokenSet('<?php
function foo() {
}');

echo $ti;

foreach ($ti as $t) {
    echo "$t\n";
}

echo "{$ti[3]}\n";