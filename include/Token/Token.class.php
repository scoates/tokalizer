<?php

require dirname(__FILE__) . '/../Output/HtmlOutputDecoration.interface.php';

class Token implements HtmlOutputDecoration {
    
    protected static $UNIQUE_COUNTER = 0;
    
    const DIR_PREV = 1;
    const DIR_NEXT = 2;
    
    protected $tokenSet;
    protected $tokenSetCount;
    protected $setIndex;
    
    protected $type;
    protected $value;
    protected $tokenOutput = null;
    
    protected $line;
    
    protected $uniqueName;
    
    protected static $validBecomeTypes = array(
        'FunctionEndToken' => 1,
        'ClassEndToken' => 1,
        'CloseBraceToken' => 1,
        'CloseParenToken' => 1,
        'CloseBracketToken' => 1,
    );
    
    public static function conjure($token, TokenSet $tokenSet, TokenOutput $tokenOutput = null) {
        if (is_array($token)) {
            switch ($token[0]) {
                case T_INTERFACE:
                    $t = new InterfaceToken($token, $tokenSet);
                    break;
                
                case T_CLASS:
                    $t = new ClassToken($token, $tokenSet);
                    break;
                
                case T_FUNCTION:
                    $t = new FunctionToken($token, $tokenSet);
                    break;
                
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
                    return new ConstructorFunctionCallToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line, $this->uniqueName);
                }
                
                // check for other types
                if ($next[0]->value == '(' && count($prev) == 2) {
                    // if the next token is an open paren, then we have a function call:
                    switch ($prev[0]->type) {
                        case T_PAAMAYIM_NEKUDOTAYIM: // "::" (-;
                            return new StaticFunctionCallToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line, $this->uniqueName, $prev[1]);
                            break;
                        case T_OBJECT_OPERATOR:
                            return new ObjectFunctionCallToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line, $this->uniqueName, $prev[1]);
                            break;
                        
                        case T_FUNCTION:
                            return $this; // (is a function definition name)
                            break;
                        
                        default:
                            // quacks like a function call...
                            return new ProceduralFunctionCallToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line, $this->uniqueName);
                    }
                }
                
                // no match, so fall through to the default...
                
                break;
            
            case '{':
            case T_CURLY_OPEN:
            case T_DOLLAR_OPEN_CURLY_BRACES:
                return new OpenBraceToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line, $this->uniqueName);
                break;
            
            case '(':
                return new OpenParenToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line, $this->uniqueName);
                break;

            case '[':
                return new OpenBracketToken(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line, $this->uniqueName);
                break;

        }
        
        // fall through to no mutation
        return $this;
    }
    
    public function setOutput(TokenOutput $tokenOutput) {
        $this->tokenOutput = $tokenOutput;
        $this->tokenOutput->setToken($this);
    }
    
    protected function __construct($token, TokenSet $tokenSet, $setIndex = null, $line = null, $uniqueName = null) {
        if ($uniqueName) {
            $this->uniqueName = $uniqueName;
        } else {
            $this->uniqueName = self::uniqueName();
        }
        $this->tokenSet = $tokenSet;
        if ($setIndex == null) {
            $setIndex = $this->tokenSet->count() - 1;
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
        if (null === $this->tokenSetCount) { // check cache
            $this->tokenSetCount = ($this->tokenSet->count() - 1); // cache
        }
        
        if ($this->setIndex < $this->tokenSetCount) {
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
    
    public function become($type) {
        if (!isset(self::$validBecomeTypes[$type])) {
            return $this;
        }
        $new = new $type(array($this->type, $this->value), $this->tokenSet, $this->setIndex, $this->line, $this->uniqueName);
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
    
    public function getUniqueName() {
        return $this->uniqueName;
    }
    
    protected static function uniqueName() {
        return 'token' . ++self::$UNIQUE_COUNTER;
    }
    
    public function decorateRollOver() {
        return 'highlight_line(true, ' . $this->line() . ');';
    }
    public function decorateRollOut() {
        return 'highlight_line(false, ' . $this->line() . ');';
    }
    
    public function decorateTitle() {
        return '';
    }
    
}

require dirname(__FILE__) . '/MatchedToken.abstract.php';
require dirname(__FILE__) . '/OpenMatchedToken.abstract.php';
require dirname(__FILE__) . '/OpenBraceToken.class.php';
require dirname(__FILE__) . '/OpenParenToken.class.php';
require dirname(__FILE__) . '/OpenBracketToken.class.php';
require dirname(__FILE__) . '/CloseMatchedToken.abstract.php';
require dirname(__FILE__) . '/CloseBraceToken.class.php';
require dirname(__FILE__) . '/CloseParenToken.class.php';
require dirname(__FILE__) . '/CloseBracketToken.class.php';

require dirname(__FILE__) . '/InterfaceToken.class.php';
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

