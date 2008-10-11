<?php

class CloseParenToken extends CloseMatchedToken implements HtmlOutputDecoration {

    protected function __construct($token, TokenSet $Set, $setIndex=null, $line=null, $uniqueName=null) {
        parent::__construct($token, $Set, $setIndex, $line, $uniqueName);
        $this->ownTokenValues = array(')');
        $this->matchedTokenValues = array('(');
        $this->findMatchedToken();
    }

}