<?php
class FunctionDefinition extends Definition {
    protected $functionToken;
    protected $class; // ClassDefinition
    protected $visibility;
    protected $static;

    const V_PROTECTED = T_PROTECTED;
    const V_PRIVATE = T_PRIVATE;
    const V_PUBLIC = T_PUBLIC;
    
    public function __construct(FunctionToken $t) {
        $this->functionToken = $t;
        $this->name = $t->getNameToken()->getValue();
        $this->visibility = $t->getVisibility();
        $this->static = $t->getStatic();
        $this->StartToken = $t->getStartToken($this->visibility, $this->static);
        $openBrace = $t->findOpenBrace();
        $this->EndToken = $openBrace->findMatchedToken('FunctionEndToken');
        $this->class = $this->determineClass();
        $this->setOutput(new TextFunctionDefinitionOutput($this));
    }
    
    protected function determineClass() {
        foreach ($this->functionToken->set()->getClasses() as $class) {
            if ($class->occupiesLine($this->functionToken->line())) {
                return $class->getName();
            }
        }
        return null;
    }
    
    public function getClass() {
        return $this->class;
    }
    
    public function getVisibility() {
        return $this->visibility;
    }
    
    public function getFunctionToken() {
        return $this->functionToken;
    }
}