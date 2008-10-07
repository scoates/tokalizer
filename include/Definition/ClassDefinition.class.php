<?php
class ClassDefinition extends Definition {
    protected $ClassToken;
    protected $extends = null;
    protected $abstract;
    
    public function __construct(ClassToken $t) {
        $this->ClassToken = $t;
        $this->name = $t->getNameToken()->getValue();
        $this->StartToken = $t->getStartToken();
        $openBrace = $t->findOpenBrace();
        $this->EndToken = $openBrace->findMatchedToken('ClassEndToken');
        $this->abstract = $t->getAbstract();
        $this->extends = $t->getExtends();
        $this->setOutput(new TextClassDefinitionOutput($this));
    }
    
    public function getExtends() {
        return $this->extends;
    }
    
    public function getAbstract() {
        return $this->abstract;
    }
    
    public function getClassToken() {
        return $this->ClassToken;
    }
}