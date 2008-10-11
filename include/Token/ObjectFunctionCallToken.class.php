<?php
class ObjectFunctionCallToken extends FunctionCallToken {
    protected $objectToken;
    
    public function __construct($token, TokenSet $Set, $setIndex, $line, $uniqueName, Token $objectToken) {
        $this->objectToken = $objectToken;
        parent::__construct($token, $Set, $setIndex, $line, $uniqueName);
    }
    
    protected function determineClassName() {
        return "NOT IMPLEMENTED @@@ TODO";
    }
    
}