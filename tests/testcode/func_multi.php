<?php

class foo {
    public function foofunc() {}
}

class bar {
    public function barfunc() {
        $x = new foo(); // 0
        $x->foofunc();  // 1
    }
    public static function barfunc2() {}
}

function solofunc() {}

$x = new bar;    // 2
$x = new bar();  // 3
$x->foofunc();   // 4

bar::barfunc2(); // 5

solofunc();      // 6
