<?php

abstract class OpenMatchedToken extends MatchedToken {
    public function decorateTitle() {
        return 'closes on line: ' . $this->MatchedToken->line();
    }
    
    protected function getDirectionNextToken(Token $t) {
        return $t->next();
    }
    
}
