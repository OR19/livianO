<?php

use Liviano\ParamMatchers\RouteParamDateMatcher;
use PHPUnit\Framework\TestCase;

class ParamDateMatcherTest extends TestCase {
    private RouteParamDateMatcher $paramMatcher;
    public function setUp(): void {
        $this->paramMatcher = new RouteParamDateMatcher();
    }
    public function testCorrectTypeDefinition() {
        $result = $this->paramMatcher->matchDefinition( '{date:identifier}' );
        $this->assertTrue( $result );
    }
    public function testWrongTypeDefinition() {
        $wrong_types = [
            '',
            '{person:identifier}',
            '{:identifier}',
            '{1:identifier}',
            '{DATE:identifier}',
            '{_date_:identifier}',
            '{"date":identifier}',
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
        $result = $this->paramMatcher->matchDefinition( '{date:my_number}' );
        $this->assertTrue( $result );
    }
    public function testWrongNameIdentifier() {
        $wrong_identifiers = [
            '{date:my number}',
            '{date:1}',
            '{date:1identifier}',
            '{date:id!entifier}',
            '{date:ident-ifier}',
            '{date:"identifier"}',
            '{date:_}',
            '{date:}'
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
        $result = $this->paramMatcher->extractParamName( '{date:identifier}' );
        $this->assertEquals( 'identifier', $result );
    }
    public function testConvertWrongParamValues() {
        $wrong_values = [
            'hola',
            '23',
            '23.3'
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
        $result = $this->paramMatcher->convert('2023-03-18');
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'seconds', $result );
        $this->assertArrayHasKey( 'minutes', $result );
        $this->assertArrayHasKey( 'hours', $result );
        $this->assertArrayHasKey( 'mday', $result );
        $this->assertArrayHasKey( 'wday', $result );
        $this->assertArrayHasKey( 'mon', $result );
        $this->assertArrayHasKey( 'year', $result );
        $this->assertArrayHasKey( 'yday', $result );
        $this->assertArrayHasKey( 'weekday', $result );
        $this->assertArrayHasKey( 'month', $result );

        $this->assertTrue( gettype($result['seconds'] ) == 'integer' );
        $this->assertTrue( gettype($result['minutes'] ) == 'integer' );
        $this->assertTrue( gettype($result['hours'] ) == 'integer' );
        $this->assertTrue( gettype($result['mday'] ) == 'integer' );
        $this->assertTrue( gettype($result['wday'] ) == 'integer' );
        $this->assertTrue( gettype($result['mon'] ) == 'integer' );
        $this->assertTrue( gettype($result['year'] ) == 'integer' );
        $this->assertTrue( gettype($result['yday'] ) == 'integer' );
        $this->assertTrue( gettype($result['weekday'] ) == 'string' );
        $this->assertTrue( gettype($result['month'] ) == 'string' );

        $this->assertEquals( 2023, $result['year'] );
        $this->assertEquals( 03, $result['mon'] );
        $this->assertEquals( 18, $result['mday'] );
    }
}