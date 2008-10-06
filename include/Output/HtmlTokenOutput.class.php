<?php

class HtmlTokenOutput extends TextTokenOutput {
    static $SLUGS = array();
    
    public function reconstruct() {
        $anchor = '#t' . $this->Token->getSetIndex();
        if ($file = $this->Token->set()->getFile()) {
            $anchor .= self::makeSlug($file . '-' . $this->Token->getSetIndex());
        }
        $class = strtolower(get_class($this->Token));
        
        // a tag
        $ret = '<a ';
        
        // class
        $ret .= 'class="token ' . strtolower($this->Token->getName());
        $ret .= ($class == 'token' ? '' : " $class");
        if ($this->Token instanceof HtmlOutputDecoration) {
            $ret .= ($x = $this->Token->decorate_class()) ? " $x" : '';
        }
        $ret .= '" ';
        
        // title
        $ret .= 'title="Token #' . $this->Token->getSetIndex();
        $ret .= ($this->Token->getName() ? ', ' . $this->Token->getName() : '');
        if ($this->Token instanceof HtmlOutputDecoration) {
            $ret .= ($x = $this->Token->decorate_title()) ? " $x" : '';
        }
        $ret .= '" ';
        
        // name
        $ret .= 'name="' . htmlentities($anchor, ENT_QUOTES, 'UTF-8');
        $ret .= '">';
        
        // body
        $ret .= htmlentities($this->Token->getValue(), ENT_QUOTES, 'UTF-8');
        
        // closing tag
        $ret .= '</a>';
        
        return $ret;
    }
    
    public static function makeSlug($file, $suffix=0) {
        $newFile = ($suffix) ? ($file . $suffix) : $file;
        $newSlug = preg_replace('/[^a-z0-9-]/i', '-', $newFile);
        if (isset(self::$SLUGS[$newSlug])) {
            return self::makeSlug($file, $suffix+1);
        } else {
            self::$SLUGS[$newSlug] = 1;
            return $newSlug;
        }
    }
}