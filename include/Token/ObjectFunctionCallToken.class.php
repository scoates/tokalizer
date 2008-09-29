<?php
class ObjectFunctionCallToken extends FunctionCallToken {
    protected $objectToken;
    
    public function __construct($token, TokenSet $Set, $setIndex, Token $objectToken) {
        $this->objectToken = $objectToken;
        parent::__construct($token, $Set, $setIndex);
    }
    
    protected function determineClassName() {
        return "NOT IMPLEMENTED @@@ TODO";
    }
    
}