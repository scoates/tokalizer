<?php

class OpenBracketToken extends OpenMatchedToken implements HtmlOutputDecoration {

    protected function __construct($token, TokenSet $Set, $setIndex = null, $line = null) {
        parent::__construct($token, $Set, $setIndex, $line);
        $this->ownTokenValues = array('[');
        $this->matchedTokenValues = array(']');
        $this->findMatchedToken();
    }

}