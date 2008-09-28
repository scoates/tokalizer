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
        // fall through to regular Token
        return new Token($token, $Set);
    }
    
    public function mutate() {
        if (get_class($this) != __CLASS__) {
            // only mutate Tokens (not special tokens found in the first pass)
            return $this;
        }
        switch ($this->type) {
            case T_STRING:
                // T_STRING can be a function call; let's check:
                $prev = $this->getPrevTokens(2);
                $next = $this->getNextTokens(1);
                
                // first check is constructor (easy to check because of "new")
                // but constructor doesn't require parens
                if ($prev[0]->type == T_NEW) {
                    // this could be a lot cleaner with LSB
                    return new ConstructorFunctionCallToken(array($this->type, $this->value), $this->Set, $this->setIndex);
                }
                
                // check for other types
                if ($next[0]->value == '(' && count($prev) == 2) {
                    // if the next token is an open paren, then we have a function call:
                    switch ($prev[0]->type) {
                        case T_PAAMAYIM_NEKUDOTAYIM: // "::" (-;
                            return new StaticFunctionCallToken(array($this->type, $this->value), $this->Set, $this->setIndex, $prev[1]);
                            break;
                        case T_OBJECT_OPERATOR:
                            return new ObjectFunctionCallToken(array($this->type, $this->value), $this->Set, $this->setIndex, $prev[1]);
                            break;
                        
                        case T_FUNCTION:
                            return $this; // (is a function definition name)
                            break;
                        
                        default:
                            // quacks like a function call...
                            return new ProceduralFunctionCallToken(array($this->type, $this->value), $this->Set, $this->setIndex);
                    }
                }
                
                // no match, so fall through to the default...
                
                break;
        }
        // fall through to no mutation
        return $this;
    }
    
    protected function __construct($token, TokenSet $Set, $setIndex = null) {
        $this->Set = $Set;
        if ($setIndex == null) {
            $setIndex = count($Set) - 1;
        }
        $this->setIndex = $setIndex;
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
    
    public function next($debug=false) {
        if ($debug) {
            echo "SI: " . $this->setIndex . "; max: " . (count($this->Set) - 1) . "\n";
        }
        if ($this->setIndex < (count($this->Set) - 1)) {
            if ($debug) echo "HERE: ". $this->Set[$this->setIndex + 1];
            return $this->Set[$this->setIndex + 1];
        } else {
            if ($debug) echo "NOT HERE";
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
    
    protected function debugContext() {
        foreach (array_reverse($this->getPrevTokens(10)) as $tok) {
            echo "PREV: $tok\n";
        }
        echo "THIS: $this\n";
        foreach ($this->getNextTokens(10) as $tok) {
            echo "NEXT: $tok\n";
        }
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