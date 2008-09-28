<?php
class ClassDefinition extends AbstractDefinition {
    protected $classToken;
    protected $extends = null;
    protected $abstract;
    
    public function __construct(ClassToken $t) {
        $this->classToken = $t;
        $this->name = $t->getNameToken()->value();
        $this->StartToken = $t->getStartToken();
        $openBrace = $t->findOpenBrace();
        $this->EndToken = $openBrace->findMatchingBrace();
        $this->abstract = $t->getAbstract();
        $this->extends = $t->getExtends();
    }
    
    public function getExtends() {
        return $this->extends;
    }
    
    public function getAbstract() {
        return $this->abstract;
    }
}