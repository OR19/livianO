<?php
use Liviano\ParamMatchers\RouteParamStringMatcher;
use PHPUnit\Framework\TestCase;

class ParamStringMatcherTest extends TestCase {
    private RouteParamStringMatcher $paramMatcher;
    public function setUp(): void {
        $this->paramMatcher = new RouteParamStringMatcher();
    }
    public function testCorrectNameIdentifier() {
        $result = $this->paramMatcher->matchDefinition( '{my_number}' );
        $this->assertTrue( $result );
    }
    public function testWrongNameIdentifier() {
        $wrong_identifiers = [
            '{my number}',
            '{1}',
            '{1identifier}',
            '{id!entifier}',
            '{ident-ifier}',
            '{"identifier"}',
            '{_}',
            '{}'
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
        $result = $this->paramMatcher->extractParamName( '{identifier}' );
        $this->assertEquals( 'identifier', $result );
    }
    public function testConvertCorrectParamValue() {
        $result = $this->paramMatcher->convert( 'palabra' );
        $this->assertEquals( 'palabra', $result );
    }
}