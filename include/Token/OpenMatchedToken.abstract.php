<?php

abstract class OpenMatchedToken extends MatchedToken {
    public function decorate_title() {
        return __CLASS__ . ' closes on line: ' . $this->MatchedToken->line();
    }
    
    protected function getDirectionNextToken(Token $t) {
        return $t->next();
    }
    
}
