<?php

abstract class TokenOutput {
    
    const STYLE_TEXT = 1;
    const STYLE_HTML = 2;
    
    protected $Token;
    
    public function setToken(Token $Token) {
        $this->Token = $Token;
    }
    
    abstract public function render();
}