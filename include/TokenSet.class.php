<?php
require dirname(__FILE__) . '/Token.class.php';
require dirname(__FILE__) . '/AbstractDefinition.class.php';
require dirname(__FILE__) . '/ClassDefinition.class.php';
require dirname(__FILE__) . '/FunctionDefinition.class.php';

class TokenSet implements Iterator, ArrayAccess, Countable {
    
    protected $file;
    protected $index = 0;
    protected $max;
    protected $tokens = array();
    protected $currentLine = 1;
    
    // TODO:
    protected $currentContext = array('root');
    
    protected $definitions = array(
        'classes' => array(), // of ClassDefinitions
        'functions' => array(), // of FunctionDefinitions
    );

    public function __construct($source, $file = null) {
        $this->file = $file;
        foreach (token_get_all($source) as $t) {
            $this->max = count($this->tokens); // one less than the number of tokens
            $Token = Token::conjure($t, $this);
            $this->tokens[] = $Token;
            $this->currentLine += substr_count($Token->value(), "\n");
        }
        $this->parse();
    }
    
    public function __toString() {
        $tokenWidth = 0;
        $data = array();
        
        // negotiate width (pass 1)
        foreach ($this->tokens as $t) {
            $tokenWidth = max($tokenWidth, strlen($t->name()));
        }
        
        // paint (pass 2)
        $ret = '';
        foreach ($this->tokens as $t) {
            $ret .= str_pad($t->name(), $tokenWidth + 1, ' ', STR_PAD_RIGHT);
            $ret .= $t->value() . "\n";
        }
        return $ret;
    }
    
    public function currentLine() {
        return $this->currentLine;
    }
    
    protected function parse() {
        $this->parseClassDefinitions();
        $this->parseFunctionDefinitions();
    }
    
    protected function parseFunctionDefinitions() {
        foreach ($this->tokens as $t) {
            if ($t->type() == T_FUNCTION) {
                $this->definitions['functions'][] = new FunctionDefinition($t);
            }
        }
    }
    
    protected function parseClassDefinitions() {
        foreach ($this->tokens as $t) {
            if ($t->type() == T_CLASS) {
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