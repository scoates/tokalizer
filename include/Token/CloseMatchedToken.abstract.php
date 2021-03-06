<?php

abstract class CloseMatchedToken extends MatchedToken {
    public function decorateTitle() {
        return __CLASS__ . ' opens on line: ' . $this->MatchedToken->line();
    }
    
    protected function getDirectionNextToken(Token $t) {
        return $t->prev();
    }
}
