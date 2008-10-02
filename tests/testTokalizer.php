<?php
require_once 'PHPUnit/Framework.php';

require '../include/TokenSet.class.php';

class TestTokalizer extends PHPUnit_Framework_TestCase {

    protected $SetFunction;
    protected $SetClass;
    protected $SetClassExtends;
    protected $SetClassAbstract;
    protected $SetFuncMulti;

    public function setUp() {
        $this->SetFunction = new TokenSet('<?php
            function foo() {
            }'
        );
        $this->SetFunction->parse();
        
        $this->SetClass = TokenSet::fromFile('testcode/class_simple.php');
        $this->SetClass->parse();
        
        $this->SetClassExtends = TokenSet::fromFile('testcode/class_extends.php');
        $this->SetClassExtends->parse();
        
        $this->SetClassAbstract = TokenSet::fromFile('testcode/class_abstract.php');
        $this->SetClassAbstract->parse();
        
        $this->SetFuncMulti = TokenSet::fromFile('testcode/func_multi.php');
        $this->SetFuncMulti->parse();
        
    }
    
    public function testSetReconstruct() {
        $this->assertEquals(file_get_contents('testcode/class_simple.php'), $this->SetClass->reconstruct());
    }

//    public function testSetString() {
//        $this->assertEquals("T_OPEN_TAG   <?php\n\nT_WHITESPACE             \nT_FUNCTION   function\nT_WHITESPACE  \nT_STRING     foo\n             (\n             )\nT_WHITESPACE  \n             {\nT_WHITESPACE \n            \n             }\n", (string)$this->SetFunction, 'toString failing');
//    }
    
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
    
    public function testFuncMultiClass() {
        $funcs = $this->SetFuncMulti->getFunctions();
        $this->assertEquals('foofunc', $funcs[0]->name());
        $this->assertEquals('foo', $funcs[0]->getClass());
        $this->assertEquals('barfunc', $funcs[1]->name());
        $this->assertEquals('bar', $funcs[1]->getClass());
        $this->assertEquals('barfunc2', $funcs[2]->name());
        $this->assertEquals('bar', $funcs[2]->getClass());
        $this->assertEquals('solofunc', $funcs[3]->name());
        $this->assertNull($funcs[3]->getClass());
    }
    
    public function testFunctionCallsCount() {
        $calls = $this->SetFuncMulti->getFunctionCalls();
        $this->assertEquals(7, count($calls));
    }
    
    public function testFunctionCallsType() {
        $calls = $this->SetFuncMulti->getFunctionCalls();
        $this->assertTrue($calls[0] instanceof ConstructorFunctionCallToken);
        $this->assertTrue($calls[1] instanceof ObjectFunctionCallToken);
        $this->assertTrue($calls[2] instanceof ConstructorFunctionCallToken);
        $this->assertTrue($calls[3] instanceof ConstructorFunctionCallToken);
        $this->assertTrue($calls[4] instanceof ObjectFunctionCallToken);
        $this->assertTrue($calls[5] instanceof StaticFunctionCallToken);
        $this->assertTrue($calls[6] instanceof ProceduralFunctionCallToken);
    }
    
    public function testFunctionCallsFunctionName() {
        $calls = $this->SetFuncMulti->getFunctionCalls();
        $this->assertEquals('foo', $calls[0]->functionName());
        $this->assertEquals('foofunc', $calls[1]->functionName());
        $this->assertEquals('bar', $calls[2]->functionName());
        $this->assertEquals('bar', $calls[3]->functionName());
        $this->assertEquals('foofunc', $calls[4]->functionName());
        $this->assertEquals('barfunc2', $calls[5]->functionName());
        $this->assertEquals('solofunc', $calls[6]->functionName());
    }
    
    public function testFunctionCallsSimpleClassName() {
        $calls = $this->SetFuncMulti->getFunctionCalls();
        $this->assertEquals('foo', $calls[0]->className());
        $this->assertEquals('bar', $calls[2]->className());
        $this->assertEquals('bar', $calls[3]->className());
        $this->assertEquals('bar', $calls[5]->className());
        $this->assertNull($calls[6]->className());
    }

    public function testFunctionCallsComplexClassName() {
        $calls = $this->SetFuncMulti->getFunctionCalls();
        $this->assertEquals('foo', $calls[1]->className());
        $this->assertEquals('bar', $calls[4]->className());
    }
    
    public function testSetGetContext() {
        $this->assertEquals('foo (class)', $this->SetFuncMulti->getContext(3));
        $this->assertEquals('bar::barfunc()', $this->SetFuncMulti->getContext(10));
        $this->assertNull($this->SetFuncMulti->getContext(17));
    }

}