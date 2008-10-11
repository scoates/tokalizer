<?php
abstract class FunctionCallToken extends Token {
    protected $functionName;
    protected $className;
    
    abstract protected function determineClassName();
    
    public function __construct($token, TokenSet $Set, $setIndex, $line=null, $uniqueName=null) {
        parent::__construct($token, $Set, $setIndex, $line, $uniqueName);
        $this->functionName = $this->value;
        $this->className = $this->determineClassName();
    }

    public function functionName() {
        return $this->functionName;
    }
    
    public function className() {
        return $this->className;
    }
}