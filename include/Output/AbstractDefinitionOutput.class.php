<?php

abstract class DefinitionOutput {
    protected $Definition;
    
    public function setDefinition(Definition $Definition) {
        $this->Definition = $Definition;
    }
    
    abstract public function render();
}