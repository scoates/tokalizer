<?php
class ClassDefinition extends AbstractDefinition {
    protected $ClassToken;
    protected $extends = null;
    protected $abstract;
    
    public function __construct(ClassToken $t) {
        $this->ClassToken = $t;
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
    
    public function __toString() {
        $ret = "class {$this->name} (";
        $file = $this->ClassToken->Set()->getFile();
        if ($file) {
            $ret .= "file: {$file}; ";
        }
        $line = $this->StartToken->line();
        $ret .= "line: {$line})";
        return  $ret;
    }
}