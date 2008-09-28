<?php

class foo {
    public function foofunc() {}
}

class bar {
    public function barfunc() {}
    public function barfunc2() {}
}

function solofunc() {}

$x = new bar;
$x = new bar();
$x->foofunc();

solofunc();