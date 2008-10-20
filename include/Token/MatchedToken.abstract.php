<?php

abstract class MatchedToken extends Token {
    protected $MatchedToken;
    protected $matchedTokenValues = array();
    protected $ownTokenValues = array();
    
    public function getMatchedToken() {
        return $this->MatchedToken;
    }

    public function findMatchedToken($becomeType = 'Token') {
        $t = $this;
        $depth = 1;
        while ($t = $this->getDirectionNextToken($t)) {
            $br = $t->getType();
            if ($br == null) {
                $br = $t->getValue();
            }

            if (in_array($br, $this->ownTokenValues)) {
                ++$depth;
            } else if (in_array($br, $this->matchedTokenValues)) {
                --$depth;
            }

            if (0 == $depth) {
                $this->MatchedToken = $t;
                return $t->become($becomeType);
            }
        }
        return false;
    }
    
    public function decorateRollOver() {
        $ret = parent::decorateRollOver();
        $ret .='highlight_tokens(true, \'' . $this->getMatchedToken()->getUniqueName() . '\');';
        return $ret;
    }
    public function decorateRollOut() {
        $ret = parent::decorateRollOut();
        $ret .= 'highlight_tokens(false, \'' . $this->getMatchedToken()->getUniqueName() . '\');';
        return $ret;
    }
    
    abstract protected function getDirectionNextToken(Token $t);

}