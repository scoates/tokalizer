<?php
class FunctionDefinition extends Definition {
    protected $functionToken;
    protected $classDefinitionToken;
    protected $visibilityToken;
    protected $staticToken;
    protected $abstractToken;

    const V_PROTECTED = T_PROTECTED;
    const V_PRIVATE = T_PRIVATE;
    const V_PUBLIC = T_PUBLIC;
    
    public function __construct(FunctionToken $t) {
        $this->functionToken = $t;
        $this->name = $t->getNameToken()->getValue();
        $this->visibilityToken = $t->getVisibility();
        $this->staticToken = $t->getStatic();
        $this->abstractToken = $t->getAbstract();
        $this->classDefinitionToken = $this->determineClass();
        
        if ($this->abstractToken) {
            // don't fetch opening brace if this is abstract
            // TODO: fetch semicolon
        //    $this->startToken = $this->getAbstractToken();
        } else {
            $this->startToken = $t->getStartToken($this->visibilityToken, $this->staticToken);
            $openBrace = $t->findOpenBrace();
            $this->EndToken = $openBrace->findMatchedToken('FunctionEndToken');
        }
        
        $this->setOutput(new TextFunctionDefinitionOutput($this));
    }
    
    protected function determineClass() {
        foreach ($this->functionToken->set()->getClasses() as $class) {
            if ($class->occupiesLine($this->functionToken->line())) {
                return $class;
            }
        }
        return null;
    }
    
    public function getClass() {
        if ($this->classDefinitionToken) {
            return $this->classDefinitionToken->getName();
        } else {
            return null;
        }
    }
    
    public function getVisibility() {
        return $this->visibilityToken->getType();
    }
    
    public function getFunctionToken() {
        return $this->functionToken;
    }
}