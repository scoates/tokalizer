<?php
abstract class AbstractDefinition {
    protected $name;
    protected $StartToken;
    protected $EndToken;

    public function name() {
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

}