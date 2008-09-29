<?php
class ConstructorFunctionCallToken extends FunctionCallToken {
    protected function determineClassName() {
        return $this->functionName();
    }
}