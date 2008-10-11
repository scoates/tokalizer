<?php
class StaticFunctionCallToken extends FunctionCallToken {
    protected $classToken;
    
    public function __construct($token, TokenSet $Set, $setIndex, $line, $uniqueName, Token $classToken) {
        $this->classToken = $classToken;
        parent::__construct($token, $Set, $setIndex, $line, $uniqueName);
    }
    
    protected function determineClassName() {
        if ($this->classToken->getType() == T_STRING) {
            // static string
            return $this->classToken->getValue();
        } else {
            throw new Exception('Invalid class name...');
        }
    }
}