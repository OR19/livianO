<?php

use Liviano\ParamMatchers\RouteParamIntMatcher;
use PHPUnit\Framework\TestCase;

class ParamIntMatcherTest extends TestCase {
    private RouteParamIntMatcher $paramMatcher;
    public function setUp(): void {
        $this->paramMatcher = new RouteParamIntMatcher();
    }
    public function testCorrectTypeDefinition() {
        $result = $this->paramMatcher->matchDefinition( '{int:identifier}' );
        $this->assertTrue( $result );
    }
    public function testWrongTypeDefinition() {
        $wrong_types = [
            '',
            '{person:identifier}',
            '{:identifier}',
            '{1:identifier}',
            '{INT:identifier}',
            '{_int_:identifier}',
            '{"int":identifier}',
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
        $result = $this->paramMatcher->matchDefinition( '{int:my_number}' );
        $this->assertTrue( $result );
    }
    public function testWrongNameIdentifier() {
        $wrong_identifiers = [
            '{int:my number}',
            '{int:1}',
            '{int:1identifier}',
            '{int:id!entifier}',
            '{int:ident-ifier}',
            '{int:"identifier"}',
            '{int:_}',
            '{int:}'
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
        $result = $this->paramMatcher->extractParamName( '{int:identifier}' );
        $this->assertEquals( 'identifier', $result );
    }
    public function testConvertWrongParamValues() {
        $wrong_values = [
            'hola',
            '2023-05-20',
            '20.23',
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
        $result = $this->paramMatcher->convert('5');
        $this->assertEquals( 5, $result );
    }
}