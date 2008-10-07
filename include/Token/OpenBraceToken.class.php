<?php

class OpenBraceToken extends OpenMatchedToken implements HtmlOutputDecoration {

    protected function __construct($token, TokenSet $Set, $setIndex = null, $line = null) {
        parent::__construct($token, $Set, $setIndex, $line);
        $this->matchedTokenValue = '}';
        $this->findMatchedToken();
    }

}