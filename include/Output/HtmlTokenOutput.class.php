<?php

class HtmlTokenOutput extends TextTokenOutput {
    public function reconstruct() {
        $anchor = '#t' . $this->Token->getSetIndex();
        if ($file = $this->Token->set()->getFile()) {
            // @@@ TODO: duplicate check
            $anchor .= $file;
        }
        $ret = '<a name="' . htmlentities($anchor, ENT_QUOTES, 'UTF8') . '">' . $this->Token->getValue() . '</a>';
        return $ret;
    }
}