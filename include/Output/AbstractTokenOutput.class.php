<?php

abstract class TokenOutput {
    protected $Token;
    
    public function setToken(Token $Token) {
        $this->Token = $Token;
    }
    
    abstract public function render();
}