<?php
abstract class Definition {
    protected $name;
    protected $startToken;
    protected $EndToken;
    protected $Output = null;

    public function getName() {
        return $this->name;
    }
    
    public function startToken() {
        return $this->startToken;
    }
    
    public function endToken() {
        return $this->EndToken;
    }
    
    public function occupiesLine($line) {
        return $this->startToken->line() <= $line && $this->EndToken->line() >= $line;
    }
    
    public function setOutput(DefinitionOutput $Output) {
        $this->Output = $Output;
        $this->Output->setDefinition($this);
    }

    public function __toString() {
        if ($this->Output === null) {
            throw new Exception('Defintion must have an Output');
        }
        return $this->Output->render();
    }
}