<?php

class CloseBraceToken extends CloseMatchedToken implements HtmlOutputDecoration {

    public function __construct($token, TokenSet $Set, $setIndex=null, $line=null, $uniqueName=null) {
        parent::__construct($token, $Set, $setIndex, $line, $uniqueName);
        $this->ownTokenValues = array('}');
        $this->matchedTokenValues = array('{', T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES);
        $this->findMatchedToken();
    }

}