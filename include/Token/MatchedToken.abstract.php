<?php

abstract class MatchedToken extends Token {
    protected $MatchedToken;
    protected $matchedTokenValue;
    
    public function getMatchedToken() {
        return $this->MatchedToken;
    }
}