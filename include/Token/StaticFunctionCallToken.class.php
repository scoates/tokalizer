<?php
class StaticFunctionCallToken extends FunctionCallToken {
    protected $classToken;
    
    public function __construct($token, TokenSet $Set, $setIndex = null, Token $classToken) {
        $this->classToken = $classToken;
        parent::__construct($token, $Set, $setIndex);
    }
    
    protected function determineClassName() {
        if ($this->classToken->type() == T_STRING) {
            // static string
            $this->className = $this->classToken->name();
        } else {
            throw new Exception('Invalid class name...');
        }
    }
}