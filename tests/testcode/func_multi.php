<?php

class foo { // classdef 0
    public function foofunc() {} // funcdef 0
}

class bar { // classdef 1
    public function barfunc() { // funcdef 1
        $x = new foo(); // call 0
        $x->foofunc();  // call 1
    }
    public static function barfunc2() {} // funcdef 2
}

function solofunc() {} // funcdef 3

$x = new bar;    // call 2
$x = new bar();  // call 3
$x->foofunc();   // call 4

bar::barfunc2(); // call 5

solofunc();      // call 6
