<?php

class FunctionToken extends Token {
    
    public static function conjure() {
        throw new Exception("Don't conjure specific types of token, use Token::conjure() instead");
    }
    
    public function getNameToken() {
        $nameToken = $this->getNextTokens(1);
        return $nameToken[0];
    }

    public function getVisibility() {
        // fetch the two (or fewer) previous tokens
        $visibility = null;
        foreach ($this->getPrevTokens(2) as $prev) {
            // check those tokens for visibility;
            switch ($prev->type()) {
                case T_PUBLIC:
                case T_PROTECTED:
                case T_PPRIVATE:
                    $visibility = $prev->type();
                    break;
            }
        }
        return $visibility;
    }

    public function getStatic() {
        // fetch the two (or fewer) previous tokens
        $static = null;
        foreach ($this->getPrevTokens(2) as $prev) {
            // check those tokens for static
            switch ($prev->type()) {
                case T_STATIC:
                    $static = true;
                    break;
            }
        }
        return $static;
    }
    
    public function getStartToken($visibility, $static) {
        // default to no visibility, not static:
        $startToken = $this;
        
        // if static or visibility is one of the previous token, the function starts there:
        if ($static || $visibility) {
            list($startToken) = $this->getPrevTokens(1);
        }
        // however, if they're BOTH set, then we need to go two tokens (plus whitespace) back:
        if ($static && $visibility) {
            list(,$startToken) = $this->getPrevTokens(2);
        }
        
        return $startToken;
    }
    
    public static function visibilityName($visibility) {
        switch ($visibility) {
            case T_PUBLIC:
                return 'public';
                break;
            case T_PROTECTED:
                return 'protected';
                break;
            case T_PRIVATE:
                return 'private';
                break;
            default:
                throw new Exception('Invalid visibility');
        }
    }

}
