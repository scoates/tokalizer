<?php

class InterfaceToken extends Token implements HtmlOutputDecoration {
    
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
        return 'interface ' . $this->getNameToken()->getValue();
    }

    public function decorateRollover() {
        return '';
    }
}