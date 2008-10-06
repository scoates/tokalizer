<?php

class OpenBraceToken extends Token implements HtmlOutputDecoration {
    
    protected $ClosingBrace;
    
    protected function __construct($token, TokenSet $Set, $setIndex = null, $line = null) {
        parent::__construct($token, $Set, $setIndex, $line);
        $this->findMatchingBrace();
    }

    public function findMatchingBrace($becomeType = 'Token') {
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
                $this->ClosingBrace = $t;
                return $t->become($becomeType);
            }
        }
        return false;
    }
    
    public function decorate_title() {
        return __CLASS__ . ' closes on line: ' . $this->ClosingBrace->line();
    }

}