<?php
abstract class Definition {
    protected $name;
    protected $StartToken;
    protected $EndToken;
    protected $Output = null;

    public function getName() {
        return $this->name;
    }
    
    public function startToken() {
        return $this->StartToken;
    }
    
    public function endToken() {
        return $this->EndToken;
    }
    
    public function occupiesLine($line) {
        return $this->StartToken->line() <= $line && $this->EndToken->line() >= $line;
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