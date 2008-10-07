<?php

abstract class OpenMatchedToken extends MatchedToken {
    public function decorate_title() {
        return __CLASS__ . ' closes on line: ' . $this->MatchedToken->line();
    }
    
    public function findMatchedToken($becomeType = 'Token') {
        $t = $this;
        $depth = 1;
        while ($t = $t->next()) {
            $br = $t->getType();
            if ($br == null) {
                $br = $t->getValue();
            }
            switch ($br) {
                case '{':
                case T_CURLY_OPEN:
                case T_DOLLAR_OPEN_CURLY_BRACES:
                    ++$depth;
                    break;
                case '}':
                    --$depth;
                    break;
            }
            if (0 == $depth) {
                $this->MatchedToken = $t;
                return $t->become($becomeType);
            }
        }
        return false;
    }
}
