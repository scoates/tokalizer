<?php

require dirname(__FILE__) . '/ClassToken.class.php';
require dirname(__FILE__) . '/FunctionToken.class.php';
require dirname(__FILE__) . '/AbstractFunctionCallToken.class.php';
require dirname(__FILE__) . '/ProceduralFunctionCallToken.class.php';
require dirname(__FILE__) . '/ConstructorFunctionCallToken.class.php';
require dirname(__FILE__) . '/StaticFunctionCallToken.class.php';
require dirname(__FILE__) . '/ObjectFunctionCallToken.class.php';

class Token {
    
    const DIR_PREV = 1;
    const DIR_NEXT = 2;
    
    protected $Set;
    protected $setIndex;
    
    protected $type;
    protected $value;
    
    protected $line;
    
    public static function conjure($token, TokenSet $Set) {
        if (is_array($token)) {
            switch ($token[0]) {
                case T_CLASS:
                    return new ClassToken($token, $Set);
                    break; // semantics (-:
                case T_FUNCTION:
                    return new FunctionToken($token, $Set);
                    break; // semantics (-:
            }
        }
        // fallthrough to regular Token
        return new Token($token, $Set);
    }
    
    private function __construct($token, TokenSet $Set) {
        $this->Set = $Set;
        $this->setIndex = count($Set) - 1;
        if (is_array($token)) {
            $this->type = $token[0];
            $this->value = $token[1];
        } else {
            $this->type = null;
            $this->value = $token;
        }
        $this->line = $Set->currentLine();
    }
    
    public function __toString() {
        return $this->name() . '(#' . $this->setIndex . ') ' . $this->value();
    }
    
    public function name() {
        return $this->type ? token_name($this->type) : null;
    }
    
    public function value() {
        return $this->value;
    }
    
    public function type() {
        return $this->type;
    }
    
    public function next() {
        if ($this->setIndex < count($this->Set) - 1) {
            return $this->Set[$this->setIndex + 1];
        } else {
            return false;
        }
    }
    
    public function prev() {
        if ($this->setIndex > 0) {
            return $this->Set[$this->setIndex - 1];
        } else {
            return false;
        }
    }
    
    public function index() {
        return $this->setIndex;
    }
    
    public function line() {
        return $this->line;
    }
    
    public function set() {
        return $this->Set;
    }
    
    protected function ensureValue($value) {
        if ($this->value != $value) {
            throw new Exception('Must only be called when value is ' . $value);
        }
    }
    
    public function findOpenBrace() {
        $t = $this;
        while ($t = $t->next()) {
            if ($t->value() == '{') {
                return $t;
            }
        }
        return false;
    }
    
    public function findMatchingBrace() {
        $this->ensureValue('{');
        $t = $this;
        $depth = 1;
        while ($t = $t->next()) {
            $br = $t->type();
            if ($br == null) {
                $br = $t->value();
            }
            switch ($br) {
                case '{':
                case T_CURLY_OPEN:
                case T_DOLLAR_OPEN_CURLY_BRACES:
                    ++$depth;
                    break;
                case '}':
                    --$depth;
                    break;
            }
            if (0 == $depth) {
                return $t;
            }
        }
        return false;
    }
    
    protected function getTokens($direction, $num, $skipWhitespace) {
        $found = 0;
        $tokens = array();
        $t = $this;
        while ($found < $num) {
            switch ($direction) {
                case self::DIR_PREV:
                    $t = $t->prev();
                    break;
                case self::DIR_NEXT:
                    $t = $t->next();
                    break;
                default:
                    throw new Exception('Invalid direction');
            }
            if (!$t) {
                // ran out of tokens...
                break;
            }
            if (!$skipWhitespace || $t->type() != T_WHITESPACE) {
                ++$found;
                $tokens[] = $t;
            }
        }
        return $tokens;
    }
    
    public function getPrevTokens($num, $skipWhitespace = true) {
        return $this->getTokens(self::DIR_PREV, $num, $skipWhitespace);
    }
    
    public function getNextTokens($num, $skipWhitespace = true) {
        return $this->getTokens(self::DIR_NEXT, $num, $skipWhitespace);
    }
    
}