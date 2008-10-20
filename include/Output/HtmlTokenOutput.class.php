<?php

class HtmlTokenOutput extends TextTokenOutput {
    public function reconstruct() {
        $id = $this->Token->getUniqueName();
        $class = strtolower(get_class($this->Token));
        
        // a tag
        $ret = '<a ';
        
        // id
        $ret .= 'id="' . $id . '" ';
        
        // class
        $ret .= 'class="token';
        if ($n = strtolower($this->Token->getName())) {
            $ret .= " $n";
        }
        $ret .= ($class == 'token' ? '' : " $class");
        $ret .= '" ';
        
        // title
        $ret .= 'title="' . get_class($this->Token) . ' #' . $this->Token->getSetIndex();
        $ret .= " id(" . $this->Token->getUniqueName() .")";
        $ret .= " line#" . $this->Token->line();
        if ($this->Token instanceof MatchedToken) {
            $ret .= " match(" . $this->Token->getMatchedToken()->getUniqueName() .") ";
        }
        $ret .= ($this->Token->getName() ? ' ' . $this->Token->getName() : '');
        if ($this->Token instanceof HtmlOutputDecoration) {
            $ret .= ($x = $this->Token->decorateTitle()) ? " $x" : '';
        }
        $ret .= '" ';
        
        // name
        $ret .= 'name="' . htmlentities($id, ENT_QUOTES, 'UTF-8') .'" ';
        
        // roll over
        if ($this->Token instanceof HtmlOutputDecoration) {
            $ret .= ' onmouseover="' . $this->Token->decorateRollOver() .'"';
            $ret .= ' onmouseout="' . $this->Token->decorateRollOut() .'"';
        }
        
        $ret .= '>';
        
        // body
        $ret .= str_replace(
            array("\t",' ',"\n"),
            array(
                '    ',   // tab-width: 4
                '&nbsp;',
                (($this->Token->getType() == T_WHITESPACE) ? '&nbsp;' : '') . "<br />\n"
                // inject space before line break (in whitespace) for visibility
            ),
            htmlentities($this->Token->getValue(), ENT_QUOTES, 'UTF-8')
        );
        
        // closing tag
        $ret .= '</a>';
        
        return $ret;
    }
    
}