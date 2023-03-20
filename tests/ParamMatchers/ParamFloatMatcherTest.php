<?php

use Liviano\ParamMatchers\RouteParamFloatMatcher;
use PHPUnit\Framework\TestCase;

class ParamFloatMatcherTest extends TestCase {
    private RouteParamFloatMatcher $paramMatcher;
    public function setUp(): void {
        $this->paramMatcher = new RouteParamFloatMatcher();
    }
    public function testCorrectTypeDefinition() {
        $result = $this->paramMatcher->matchDefinition( '{float:identifier}' );
        $this->assertTrue( $result );
    }
    public function testWrongTypeDefinition() {
        $wrong_types = [
            '',
            '{person:identifier}',
            '{:identifier}',
            '{1:identifier}',
            '{FLAOT:identifier}',
            '{_float_:identifier}',
            '{"float":identifier}',
        ];
        $result = false;
        $item_passed = '';
        foreach( $wrong_types as $wrong_type ) {
            if( $this->paramMatcher->matchDefinition( $wrong_type ) ) {
                $result = true;
                $item_passed = $wrong_type;
                break;
            }
        }
        $this->assertFalse( $result, "A bad type has sneaked the test: $item_passed" );
    }
    public function testCorrectNameIdentifier() {
        $result = $this->paramMatcher->matchDefinition( '{float:my_number}' );
        $this->assertTrue( $result );
    }
    public function testWrongNameIdentifier() {
        $wrong_identifiers = [
            '{float:my number}',
            '{float:1}',
            '{float:1identifier}',
            '{float:id!entifier}',
            '{float:ident-ifier}',
            '{float:"identifier"}',
            '{float:_}',
            '{float:}'
        ];
        $result = false;
        $item_passed = '';
        foreach( $wrong_identifiers as $wrong_identifier ) {
            if( $this->paramMatcher->matchDefinition( $wrong_identifier ) ) {
                $result = true;
                $item_passed = $wrong_identifier;
                break;
            }
        }
        $this->assertFalse( $result, "A bad identifier has sneaked the test: $item_passed" );
    }
    public function testExtractParamName() {
        $result = $this->paramMatcher->extractParamName( '{float:identifier}' );
        $this->assertEquals( 'identifier', $result );
    }
    public function testConvertWrongParamValues() {
        $wrong_values = [
            'hola',
            '2023-05-20',
        ];
        $result = false;
        $item_passed = '';
        foreach( $wrong_values as $wrong_value ) {
            if( gettype($this->paramMatcher->convert( $wrong_value )) != 'boolean' ) {
                $result = true;
                $item_passed = $wrong_value;
                break;
            }
        }
        $this->assertFalse( $result, "A bad value has sneaked the test: $item_passed" );
    }
    public function testConvertCorrectParamValue() {
        $result = $this->paramMatcher->convert('5.3');
        $this->assertEquals( 5.3, $result );
    }
}