<?php

class ClassToken extends Token implements HtmlOutputDecoration {
    
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
    
    public function getExtends() {
        $next = $this->getNextTokens(3); // class foo(0) extends(1) bar(2)
        if (count($next) == 3 && $next[1]->getType() == T_EXTENDS) {
            return $next[2]->getValue();
        } else {
            return null;
        }
    }
    
    public function getAbstract() {
        $prev = $this->getPrevTokens(1); // abstract(0) class
        return $prev[0]->getType() == T_ABSTRACT;
    }
    
    public function decorateTitle() {
        return 'class ' . $this->getNameToken()->getValue();
    }

    public function decorateRollover() {
        return '';
    }
}