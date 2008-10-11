<?php

class OpenBraceToken extends OpenMatchedToken implements HtmlOutputDecoration {

    protected function __construct($token, TokenSet $Set, $setIndex=null, $line=null, $uniqueName=null) {
        parent::__construct($token, $Set, $setIndex, $line, $uniqueName);
        $this->ownTokenValues = array(T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES, '{');
        $this->matchedTokenValues = array('}');
        $this->findMatchedToken('CloseBraceToken');
    }

}