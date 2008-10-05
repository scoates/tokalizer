<?php

class TextTokenOutput extends TokenOutput {
    public function render() {
        return $this->Token->name() . '(#' . $this->Token->getSetIndex() . ') ' . $this->Token->value();
    }
}