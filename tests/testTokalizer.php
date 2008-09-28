<?php
require_once 'PHPUnit/Framework.php';

require '../include/TokenSet.class.php';

class TestTokalizer extends PHPUnit_Framework_TestCase {

    protected $SetFunction;
    protected $SetClass;
    protected $SetClassExtends;
    protected $SetClassAbstract;

    public function setUp() {
        $this->SetFunction = new TokenSet('<?php
            function foo() {
            }'
        );
        
        $this->SetClass = TokenSet::fromFile('testcode/class_simple.php');
        
        $this->SetClassExtends = TokenSet::fromFile('testcode/class_extends.php');
        
        $this->SetClassAbstract = TokenSet::fromFile('testcode/class_abstract.php');
        
    }

    public function testSetString() {
        $this->assertEquals("T_OPEN_TAG   <?php\n\nT_WHITESPACE             \nT_FUNCTION   function\nT_WHITESPACE  \nT_STRING     foo\n             (\n             )\nT_WHITESPACE  \n             {\nT_WHITESPACE \n            \n             }\n", (string)$this->SetFunction, 'toString failing');
    }
    
    public function testTokenString() {
        $this->assertEquals('T_FUNCTION(#2) function', (string)$this->SetFunction[2]);
    }
    
    public function testTokenName() {
        $this->assertEquals('T_FUNCTION', $this->SetFunction[2]->name());
    }
    
    public function testTokenType() {
        $this->assertEquals(T_FUNCTION, $this->SetFunction[2]->type());
    }
    
    public function testTokenValue() {
        $this->assertEquals('function', $this->SetFunction[2]->value());
    }
    
    public function testSetCount() {
        $this->assertEquals(count($this->SetFunction), 11);
        $this->assertNull($this->SetFunction[count($this->SetFunction)]);
    }
    
    public function testTokenNext() {
        $this->assertEquals('T_FUNCTION(#2) function', (string)$this->SetFunction[1]->next());
        $this->assertFalse($this->SetFunction[count($this->SetFunction) - 1]->next());
    }

    public function testTokenPrev() {
        $this->assertEquals('T_FUNCTION(#2) function', (string)$this->SetFunction[3]->prev());
        $this->assertFalse($this->SetFunction[0]->prev());
    }
    
    public function testTokenLine() {
        $this->assertEquals(2, $this->SetFunction[2]->line());
    }
    
    public function testTokenIndex() {
        $this->assertEquals(4, $this->SetFunction[4]->index());
    }
    
    public function testTokenFindOpenBrace() {
        $this->assertEquals(8, $this->SetFunction[1]->findOpenBrace()->index());
    }
    
    public function testTokenFindMatchingBrace() {
        $this->assertEquals(10, $this->SetFunction[8]->findMatchingBrace()->index());
    }
    
    public function testSetGetFunctions() {
        $this->assertEquals(1, count($this->SetFunction->getFunctions()));
    }
    
    public function testSetGetClasses() {
        $this->assertEquals(1, count($this->SetClass->getClasses()));
    }
    
    public function testTokenClassExtendsNull() {
        $classes = $this->SetClass->getClasses();
        $this->assertNull($classes[0]->getExtends());
    }
    
    public function testSetFunctionClass() {
        $functions = $this->SetClass->getFunctions();
        $this->assertEquals('foo', $functions[0]->getClass());
    }
    
    public function testTokenClassExtends() {
        $classes = $this->SetClassExtends->getClasses();
        $this->assertEquals('baz', $classes[0]->getExtends());
    }
    
    public function testTokenClassAbstract() {
        $classes = $this->SetClass->getClasses();
        $this->assertFalse($classes[0]->getAbstract());
        $classes = $this->SetClassAbstract->getClasses();
        $this->assertTrue($classes[0]->getAbstract());
    }
    
}