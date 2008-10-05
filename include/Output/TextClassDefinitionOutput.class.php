<?php

class TextClassDefinitionOutput extends DefinitionOutput {
    public function render() {
        $ret = 'class ' . $this->Definition->getName() . '(';
        $file = $this->Definition->getClassToken()->Set()->getFile();
        if ($file) {
            $ret .= "file: {$file}; ";
        }
        $line = $this->Definition->startToken()->line();
        $endLine = $this->Definition->endToken()->line();
        $ret .= "line(s): {$line} to {$endLine})";
        return  $ret;
    }
}