<?php

class InterfaceToken extends Token {
    
    public static function conjure() {
        throw new Exception("Don't conjure specific types of token, use Token::conjure() instead");
    }
    
    public function getNameToken() {
        $nameToken = $this->getNextTokens(1);
        return $nameToken[0];
    }
    
    public function getStartToken() {
        return $this;
    }
    
    public function decorateTitle() {
        return parent::decorateTitle() . ' interface ' . $this->getNameToken()->getValue();
    }

}