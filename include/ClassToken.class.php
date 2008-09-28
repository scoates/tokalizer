<?php

class ClassToken extends Token {
    
    public static function conjure() {
        throw new Exception("Don't conjure specific types of token, use Token::conjure() instead");
    }
    
    public function getNameToken() {
        $nameToken = $this->getNextTokens(1, true);
        return $nameToken[0];
    }
    
    public function getStartToken() {
        return $this;
    }
    
    public function getExtends() {
        $next = $this->getNextTokens(3, true); // class foo(0) extends(1) bar(2)
        if (count($next) == 3 && $next[1]->type() == T_EXTENDS) {
            return $next[2]->value();
        } else {
            return null;
        }
    }
    
    public function getAbstract() {
        $prev = $this->getPrevTokens(1, true); // abstract(0) class
        return $prev[0]->type() == T_ABSTRACT;
    }
}