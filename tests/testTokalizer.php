<?php
require_once 'PHPUnit/Framework.php';

require '../include/TokenSet.class.php';
require '../include/Output/HtmlTokenOutput.class.php';

class TestTokalizer extends PHPUnit_Framework_TestCase {

    protected $setFunction;
    protected $setClass;
    protected $setClassExtends;
    protected $setClassAbstract;
    protected $setFuncMulti;

    public function setUp() {
        $this->setFunction = new TokenSet('<?php
            function foo() {
            }'
        );
        $this->setFunction->parse();
        
        $this->setClass = TokenSet::fromFile('testcode/class_simple.php');
        $this->setClass->parse();
        
        $this->setClassExtends = TokenSet::fromFile('testcode/class_extends.php');
        $this->setClassExtends->parse();
        
        $this->setClassAbstract = TokenSet::fromFile('testcode/class_abstract.php');
        $this->setClassAbstract->parse();
        
        $this->setFuncMulti = TokenSet::fromFile('testcode/func_multi.php');
        $this->setFuncMulti->parse();
        
        $this->setAbstractInterface = TokenSet::fromFile('testcode/abstract_interface.php');
        //$this->setAbstractInterface->parse();
    }
    
    public function testSetReconstruct() {
        $this->assertEquals(file_get_contents('testcode/class_simple.php'), $this->setClass->reconstruct());
    }

//    public function testSetString() {
//        $this->assertEquals("T_OPEN_TAG   <?php\n\nT_WHITESPACE             \nT_FUNCTION   function\nT_WHITESPACE  \nT_STRING     foo\n             (\n             )\nT_WHITESPACE  \n             {\nT_WHITESPACE \n            \n             }\n", (string)$this->setFunction, 'toString failing');
//    }
    
    public function testTokenString() {
        $this->assertEquals('T_FUNCTION(#2) function', (string)$this->setFunction[2]);
    }
    
    public function testTokenName() {
        $this->assertEquals('T_FUNCTION', $this->setFunction[2]->getName());
    }
    
    public function testTokenType() {
        $this->assertEquals(T_FUNCTION, $this->setFunction[2]->getType());
    }
    
    public function testTokenValue() {
        $this->assertEquals('function', $this->setFunction[2]->getValue());
    }
    
    public function testSetCount() {
        $this->assertEquals(count($this->setFunction), 11);
        $this->assertNull($this->setFunction[count($this->setFunction)]);
    }
    
    public function testTokenNext() {
        $this->assertEquals('T_FUNCTION(#2) function', (string)$this->setFunction[1]->next());
        $this->assertFalse($this->setFunction[count($this->setFunction) - 1]->next());
    }

    public function testTokenPrev() {
        $this->assertEquals('T_FUNCTION(#2) function', (string)$this->setFunction[3]->prev());
        $this->assertFalse($this->setFunction[0]->prev());
    }
    
    public function testTokenLine() {
        $this->assertEquals(2, $this->setFunction[2]->line());
    }
    
    public function testTokenIndex() {
        $this->assertEquals(4, $this->setFunction[4]->index());
    }
    
    public function testTokenFindOpenBrace() {
        $this->assertEquals(8, $this->setFunction[1]->findOpenBrace()->index());
    }
    
    public function testTokenFindMatchingBrace() {
        $this->assertEquals(10, $this->setFunction[8]->findMatchedToken()->index());
    }
    
    public function testSetGetFunctions() {
        $this->assertEquals(1, count($this->setFunction->getFunctions()));
    }
    
    public function testSetGetClasses() {
        $this->assertEquals(1, count($this->setClass->getClasses()));
    }
    
    public function testTokenClassExtendsNull() {
        $classes = $this->setClass->getClasses();
        $this->assertNull($classes[0]->getExtends());
    }
    
    public function testSetFunctionClass() {
        $functions = $this->setClass->getFunctions();
        $this->assertEquals('foo', $functions[0]->getClass());
    }
    
    public function testTokenClassExtends() {
        $classes = $this->setClassExtends->getClasses();
        $this->assertEquals('baz', $classes[0]->getExtends());
    }
    
    public function testTokenClassAbstract() {
        $classes = $this->setClass->getClasses();
        $this->assertFalse($classes[0]->getAbstract());
        $classes = $this->setClassAbstract->getClasses();
        $this->assertTrue($classes[0]->getAbstract());
    }
    
    public function testFuncMultiClass() {
        $funcs = $this->setFuncMulti->getFunctions();
        $this->assertEquals('foofunc', $funcs[0]->getName());
        $this->assertEquals('foo', $funcs[0]->getClass());
        $this->assertEquals('barfunc', $funcs[1]->getName());
        $this->assertEquals('bar', $funcs[1]->getClass());
        $this->assertEquals('barfunc2', $funcs[2]->getName());
        $this->assertEquals('bar', $funcs[2]->getClass());
        $this->assertEquals('solofunc', $funcs[3]->getName());
        $this->assertNull($funcs[3]->getClass());
    }
    
    public function testFunctionCallsCount() {
        $calls = $this->setFuncMulti->getFunctionCalls();
        $this->assertEquals(7, count($calls));
    }
    
    public function testFunctionCallsType() {
        $calls = $this->setFuncMulti->getFunctionCalls();
        $this->assertTrue($calls[0] instanceof ConstructorFunctionCallToken);
        $this->assertTrue($calls[1] instanceof ObjectFunctionCallToken);
        $this->assertTrue($calls[2] instanceof ConstructorFunctionCallToken);
        $this->assertTrue($calls[3] instanceof ConstructorFunctionCallToken);
        $this->assertTrue($calls[4] instanceof ObjectFunctionCallToken);
        $this->assertTrue($calls[5] instanceof StaticFunctionCallToken);
        $this->assertTrue($calls[6] instanceof ProceduralFunctionCallToken);
    }
    
    public function testFunctionCallsFunctionName() {
        $calls = $this->setFuncMulti->getFunctionCalls();
        $this->assertEquals('foo', $calls[0]->functionName());
        $this->assertEquals('foofunc', $calls[1]->functionName());
        $this->assertEquals('bar', $calls[2]->functionName());
        $this->assertEquals('bar', $calls[3]->functionName());
        $this->assertEquals('foofunc', $calls[4]->functionName());
        $this->assertEquals('barfunc2', $calls[5]->functionName());
        $this->assertEquals('solofunc', $calls[6]->functionName());
    }
    
    public function testFunctionCallsSimpleClassName() {
        $calls = $this->setFuncMulti->getFunctionCalls();
        $this->assertEquals('foo', $calls[0]->className());
        $this->assertEquals('bar', $calls[2]->className());
        $this->assertEquals('bar', $calls[3]->className());
        $this->assertEquals('bar', $calls[5]->className());
        $this->assertNull($calls[6]->className());
    }
/*
    public function testFunctionCallsComplexClassName() {
        $calls = $this->setFuncMulti->getFunctionCalls();
        $this->assertEquals('foo', $calls[1]->className());
        $this->assertEquals('bar', $calls[4]->className());
    }
*/
    
    public function testSetGetContext() {
        $this->assertEquals('foo (class)', $this->setFuncMulti->getContext(3));
        $this->assertEquals('bar::barfunc()', $this->setFuncMulti->getContext(10));
        $this->assertNull($this->setFuncMulti->getContext(17));
    }
    
}