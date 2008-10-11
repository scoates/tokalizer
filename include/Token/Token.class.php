<?php

require dirname(__FILE__) . '/../Output/HtmlOutputDecoration.interface.php';

require dirname(__FILE__) . '/MatchedToken.abstract.php';
require dirname(__FILE__) . '/OpenMatchedToken.abstract.php';
require dirname(__FILE__) . '/OpenBraceToken.class.php';
require dirname(__FILE__) . '/OpenParenToken.class.php';
require dirname(__FILE__) . '/OpenBracketToken.class.php';
require dirname(__FILE__) . '/CloseMatchedToken.abstract.php';
require dirname(__FILE__) . '/CloseBraceToken.class.php';
require dirname(__FILE__) . '/CloseParenToken.class.php';

require dirname(__FILE__) . '/ClassToken.class.php';
require dirname(__FILE__) . '/ClassEndToken.class.php';

require dirname(__FILE__) . '/FunctionToken.class.php';
require dirname(__FILE__) . '/FunctionEndToken.class.php';

require dirname(__FILE__) . '/AbstractFunctionCallToken.class.php';
require dirname(__FILE__) . '/ProceduralFunctionCallToken.class.php';
require dirname(__FILE__) . '/ConstructorFunctionCallToken.class.php';
require dirname(__FILE__) . '/StaticFunctionCallToken.class.php';
require dirname(__FILE__) . '/ObjectFunctionCallToken.class.php';

require dirname(__FILE__) . '/../Output/AbstractTokenOutput.class.php';
require dirname(__FILE__) . '/../Output/TextTokenOutput.class.php';

class Token {
    
    const DIR_PREV = 1;
    const DIR_NEXT = 2;
    
    protected $tokenSet;
    protected $setIndex;
    
    protected $type;
    protected $value;
    protected $tokenOutput = null;
    
    protected $line;
    
    public static function conjure($token, TokenSet $tokenSet, TokenOutput $tokenOutput = null) {
        if (is_array($token)) {
            switch ($token[0]) {
                case T_CLASS:
                    $t = new ClassToken($token, $tokenSet);
                    break; // semantics (-:
                
                case T_FUNCTION:
                    $t = new FunctionToken($token, $tokenSet);
                    break; // semantics (-:
                
                default:
                    // fall through to regular Token
                    $t = new Token($token, $tokenSet);
            }
        } else {
            // fall through to regular Token
            $t = new Token($token, $tokenSet);
        }
        
        if ($tokenOutput == null) {
            $tokenOutput = new TextTokenOutput;
        }
        
        $t->setOutput($tokenOutput);
        return $t;
    }
    
    public function mutate() {
        if (get_class($this) != __CLASS__) {
            // only mutate Tokens (not special tokens found in the first pass)
            return $this;
        }
        $type = $this->type ? $this->type : $this->value;
        switch ($type) {
            case T_STRING:
                // T_STRING can be a function call; let's check:
                $prev = $this->getPrevTokens(2);
                $next = $this->getNextTokens(1);
                
                // first check is constructor (easy to check because of "new")
                // but constructor doesn't require parens
                if ($prev[0]->type == T_NEW) {
                    // this could be a lot cleaner with LSB
                    return new ConstructorFunctionCallToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex);
                }
                
                // check for other types
                if ($next[0]->value == '(' && count($prev) == 2) {
                    // if the next token is an open paren, then we have a function call:
                    switch ($prev[0]->type) {
                        case T_PAAMAYIM_NEKUDOTAYIM: // "::" (-;
                            return new StaticFunctionCallToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $prev[1]);
                            break;
                        case T_OBJECT_OPERATOR:
                            return new ObjectFunctionCallToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $prev[1]);
                            break;
                        
                        case T_FUNCTION:
                            return $this; // (is a function definition name)
                            break;
                        
                        default:
                            // quacks like a function call...
                            return new ProceduralFunctionCallToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex);
                    }
                }
                
                // no match, so fall through to the default...
                
                break;
            
            case '{':
                return new OpenBraceToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line);
                break;
            
            case '(':
                return new OpenParenToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line);
                break;

            case '[':
                return new OpenBracketToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line);
                break;

        }
        
        // fall through to no mutation
        return $this;
    }
    
    public function setOutput(TokenOutput $tokenOutput) {
        $this->tokenOutput = $tokenOutput;
        $this->tokenOutput->setToken($this);
    }
    
    protected function __construct($token, TokenSet $tokenSet, $setIndex = null, $line = null) {
        $this->tokenSet = $tokenSet;
        if ($setIndex == null) {
            $setIndex = count($this->tokenSet) - 1;
        }
        $this->setIndex = $setIndex;
        if (is_array($token)) {
            $this->type = $token[0];
            $this->value = $token[1];
        } else {
            $this->type = null;
            $this->value = $token;
        }
        $this->line = is_null($line) ? $this->tokenSet->currentLine() : $line;
    }
    
    protected function ensureOutput() {
        if ($this->tokenOutput === null) {
            // Token has no output object
            // can't throw an exception from __toString, so: default
            $this->tokenOutput = new TextTokenOutput;
            $this->tokenOutput->setToken($this);
        }
    }
    
    public function __toString() {
        $this->ensureOutput();
        return $this->tokenOutput->render();
    }
    
    public function reconstruct() {
        $this->ensureOutput();
        return $this->tokenOutput->reconstruct();
    }
    
    public function getName() {
        return $this->type ? token_name($this->type) : null;
    }
    
    public function getValue() {
        return $this->value;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function next() {
        if ($debug) {
            echo "SI: " . $this->setIndex . "; max: " . (count($this->tokenSet) - 1) . "\n";
        }
        if ($this->setIndex < (count($this->tokenSet) - 1)) {
            return $this->tokenSet[$this->setIndex + 1];
        } else {
            return false;
        }
    }
    
    public function prev() {
        if ($this->setIndex > 0) {
            return $this->tokenSet[$this->setIndex - 1];
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
        return $this->tokenSet;
    }
    
    public function findOpenBrace() {
        $t = $this;
        while ($t = $t->next()) {
            if ($t instanceof OpenBraceToken) {
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
    
    public function become($type) {
        switch ($type) {
            case 'FunctionEndToken':
                $new = new FunctionEndToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line);
                break;
            case 'ClassEndToken':
                $new = new ClassEndToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line);
                break;
            case 'CloseBraceToken':
                $new = new CloseBraceToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line);
                break;
            case 'CloseParenToken':
                $new = new CloseParenToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line);
                break;
            default:
                return $this;
        }
        $this->tokenSet->replace($this->setIndex, $new);
        return $new;
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
            if (!$skipWhitespace || $t->getType() != T_WHITESPACE) {
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
    
    public function getSetIndex() {
        return $this->setIndex;
    }
    
    public function setOutputStyle($style) {
        switch ($style) {
            case TokenOutput::STYLE_TEXT:
                if ($this->tokenOutput instanceof TextTokenOutput) {
                    // no need to change, so short circuit
                    return;
                }
                $this->tokenOutput = new TextTokenOutput;
                break;
            
            case TokenOutput::STYLE_HTML:
                if ($this->tokenOutput instanceof HtmlTokenOutput) {
                    // no need to change, so short circuit
                    return;
                }
                $this->tokenOutput = new HtmlTokenOutput;
                break;
            
            default:
                throw new Exception('Invalid output style');
        }
        $this->tokenOutput->setToken($this);
    }
}
