<?php
class ObjectFunctionCallToken extends FunctionCallToken {
    protected $objectToken;
    
    protected function __construct($token, TokenSet $Set, $setIndex, Token $objectToken) {
        $this->objectToken = $objectToken;
        parent::__construct($token, $Set, $setIndex);
    }
}