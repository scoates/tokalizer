<?php

class TextTokenOutput extends TokenOutput {
    public function render() {
        return $this->Token->getName() . '(#' . $this->Token->getSetIndex() . ') ' . $this->Token->getValue();
    }
    
    public function reconstruct() {
        return $this->Token->getValue();
    }
}