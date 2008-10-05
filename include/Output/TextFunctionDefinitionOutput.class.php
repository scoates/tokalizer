<?php

class TextFunctionDefinitionOutput extends DefinitionOutput {
    public function render() {
        
        $name = $this->Definition->getClass() ? ($this->Definition->getClass() . '::' . $this->Definition->getName()) : $this->Definition->getName();
        $ret = '';
        if ($this->Definition->getVisibility()) {
            $ret .= FunctionToken::visibilityName($this->Definition->getVisibility()) . ' ';
        }
        if ($this->static) {
            $ret .= 'static ';
        }
        $ret .= "function {$name} (";
        $file = $this->Definition->getFunctionToken()->Set()->getFile();
        if ($file) {
            $ret .= "file: {$file}; ";
        }
        $line = $this->Definition->startToken()->line();
        $endLine = $this->Definition->endToken()->line();
        $ret .= "line(s): {$line} to {$endLine})";
        return  $ret;
    }
}