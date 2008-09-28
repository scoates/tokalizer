<?php
class StaticFunctionCallToken extends FunctionCallToken {
    protected $classToken;
    
    protected function __construct($token, TokenSet $Set, $setIndex = null, Token $classToken) {
        $this->classToken = $classToken;
        parent::__construct($token, $Set, $setIndex);
    }
}