<?php
class FunctionDefinition extends Definition {
    protected $functionToken;
    protected $classDefinitionToken;
    protected $visibility;
    protected $isStatic;
    protected $isAbstract;

    const V_PROTECTED = T_PROTECTED;
    const V_PRIVATE = T_PRIVATE;
    const V_PUBLIC = T_PUBLIC;
    
    public function __construct(FunctionToken $t) {
        $this->functionToken = $t;
        $this->name = $t->getNameToken()->getValue();
        $this->visibility = $t->getVisibility();
        $this->isStatic = $t->getStatic();
        $this->isAbstract = $t->getAbstract();
        $this->classDefinitionToken = $this->determineClass();
        
        if ($this->isAbstract) {
            // don't fetch opening brace if this is abstract
            // TODO: fetch semicolon
        //    $this->StartToken = $this->getAbstractToken();
        } else {
            $this->StartToken = $t->getStartToken($this->visibility, $this->isStatic);
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
        return $this->visibility;
    }
    
    public function getFunctionToken() {
        return $this->functionToken;
    }
    
    protected function determineAbstract() {
        
    }
}