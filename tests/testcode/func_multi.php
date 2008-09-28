<?php

class foo {
    public function foofunc() {}
}

class bar {
    public function barfunc() {
        $x = new foo();
        $c->foofunc();
    }
    public static function barfunc2() {}
}

function solofunc() {}

$x = new bar;
$x = new bar();
$x->foofunc();

bar::barfunc2();

solofunc();
