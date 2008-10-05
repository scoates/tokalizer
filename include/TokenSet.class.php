<?php
require dirname(__FILE__) . '/Token/Token.class.php';
require dirname(__FILE__) . '/Definition/AbstractDefinition.class.php';
require dirname(__FILE__) . '/Definition/ClassDefinition.class.php';
require dirname(__FILE__) . '/Definition/FunctionDefinition.class.php';
require dirname(__FILE__) . '/Output/AbstractDefinitionOutput.class.php';
require dirname(__FILE__) . '/Output/TextClassDefinitionOutput.class.php';
require dirname(__FILE__) . '/Output/TextFunctionDefinitionOutput.class.php';

class TokenSet implements Iterator, ArrayAccess, Countable {
    
    protected $file;
    protected $index = 0;
    protected $max;
    protected $tokens = array();
    protected $currentLine = 1;
    
    protected $definitions = array(
        'classes' => array(), // of ClassDefinitions
        'functions' => array(), // of FunctionDefinitions
    );
    
    protected $functionCalls = array();
    
    public static function fromFile($file) {
        if (!is_readable($file)) {
            throw new Exception("$file is not readable");
        }
        return new self(file_get_contents($file), $file);
    }

    public function __construct($source, $file = null) {
        $this->file = $file;
        // pass 1: non-contextual tokens
        foreach (token_get_all($source) as $t) {
            $this->max = count($this->tokens); // one less than the number of tokens
            $Token = Token::conjure($t, $this);
            $this->tokens[] = $Token;
            $this->currentLine += substr_count($Token->getValue(), "\n");
        }
        // pass 2: contextual tokens
        for ($i=0; $i<count($this->tokens); $i++) {
            $this->tokens[$i] = $this->tokens[$i]->mutate();
        }
    }
    
    public function __toString() {
        $tokenWidth = 0;
        $tokenClassWidth = 0;
        $lineWidth = 0;
        $data = array();
        
        // negotiate width (pass 1)
        foreach ($this->tokens as $t) {
            $tokenWidth = max($tokenWidth, strlen($t->getName()));
            $tokenClassWidth = max($tokenClassWidth, strlen(get_class($t)));
            $lineWidth = max($lineWidth, strlen($t->line()));
        }
        
        // paint (pass 2)
        $ret = '';
        foreach ($this->tokens as $t) {
            $ret .= str_pad($t->line(), $lineWidth + 1, ' ', STR_PAD_RIGHT);
            $ret .= str_pad($t->getName(), $tokenWidth + 1, ' ', STR_PAD_RIGHT);
            $ret .= str_pad(get_class($t), $tokenClassWidth + 1, ' ', STR_PAD_RIGHT);
            $ret .= trim($t->getValue()) . "\n";
        }
        return $ret;
    }
    
    public function replace($index, Token $token) {
        $this->tokens[$index] = $token;
    }
    
    public function getContext($line) {
        $classname = '';
        foreach ($this->definitions['classes'] as $C) {
            if ($C->occupiesLine($line)) {
                $classname = $C->getName();
                break;
            }
        }
        $funcname = '';
        foreach ($this->definitions['functions'] as $F) {
            if ($F->occupiesLine($line)) {
                $funcname = $F->getName() ;
                break;
            }
        }
        if ($classname && $funcname) {
            return "{$classname}::{$funcname}()";
        } else if ($classname) {
            return "{$classname} (class)";
        } else if ($funcname) {
            return "{$funcname}()";
        }
        return null;
    }
    
    public function reconstruct() {
        $ret = '';
        foreach ($this->tokens as $t) {
            $ret .= $t->reconstruct();
        }
        return $ret;
    }
    
    public function setOutputStyle($style) {
        foreach ($this->tokens as $t) {
            $t->setOutputStyle($style);
        }
    }
    
    public function currentLine() {
        return $this->currentLine;
    }
    
    public function parse() {
        $this->parseClassDefinitions();
        $this->parseFunctionDefinitions();
        $this->parseFunctionCalls();
    }
    
    public function parseFunctionCalls() {
        $this->functionCalls = array();
        foreach ($this->tokens as $t) {
            if ($t instanceof FunctionCallToken) {
                $this->functionCalls[] = $t;
            }
        }
    }
    
    public function parseFunctionDefinitions() {
        $this->definitions['functions'] = array();
        foreach ($this->tokens as $t) {
            if ($t instanceof FunctionToken) {
                $this->definitions['functions'][] = new FunctionDefinition($t);
            }
        }
    }
    
    public function parseClassDefinitions() {
        $this->definitions['classes'] = array();
        foreach ($this->tokens as $t) {
            if ($t instanceof ClassToken) {
                $this->definitions['classes'][] = new ClassDefinition($t);
            }
        }
    }

    public function getFunctions() {
        return $this->definitions['functions'];
    }

    public function getClasses() {
        return $this->definitions['classes'];
    }
    
    public function getFile() {
        return $this->file;
    }
    
    public function getFunctionCalls() {
        return $this->functionCalls;
    }
    
    // iterator implementation
    public function rewind() {
        $this->index = 0;
    }
    public function current() {
        return $this->offsetGet($this->index);
    }
    public function key() {
        return $i;
    }
    public function next() {
        return $this->offsetGet(++$this->index);
    }
    public function valid() {
        return $this->offsetExists($this->index);
    }
    
    // arrayaccess implementation
    public function offsetExists($offset) {
        return ($offset >= 0 && $offset <= $this->max);
    }
    public function offsetGet($offset) {
        return $this->tokens[$offset];
    }
    public function offsetSet($offset, $value) {
        throw new Exception('TokenSet is not writable');
    }
    public function offsetUnset($offset) {
        throw new Exception('TokenSet is not writable');
    }
    
    // countable implementation
    public function count() {
        return $this->max + 1;
    }
}