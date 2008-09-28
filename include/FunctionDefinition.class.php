<?php
class FunctionDefinition extends AbstractDefinition {
    protected $FunctionToken;
    protected $class; // ClassDefinition
    protected $visibility;
    protected $static;

    const V_PROTECTED = T_PROTECTED;
    const V_PRIVATE = T_PRIVATE;
    const V_PUBLIC = T_PUBLIC;
    
    public function __construct(FunctionToken $t) {
        $this->FunctionToken = $t;
        $this->name = $t->getNameToken()->value();
        $this->visibility = $t->getVisibility();
        $this->static = $t->getStatic();
        $this->StartToken = $t->getStartToken($this->visibility, $this->static);
        $openBrace = $t->findOpenBrace();
        $this->EndToken = $openBrace->findMatchingBrace();
        $this->class = $this->determineClass();
    }
    
    protected function determineClass() {
        foreach ($this->FunctionToken->set()->getClasses() as $class) {
            if ($class->occupiesLine($this->FunctionToken->line())) {
                return $class->name();
            }
        }
        return null;
    }
    
    public function getClass() {
        return $this->class;
    }
}