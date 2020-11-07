<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;
use App\Models\Traits\Uuid;

class CastMemberTest extends TestCase
{
    private $castMember;

    protected function setUp() : void {
        parent::setUp();
        $this->castMember = new CastMember();
    }    

    public function testIsAttributeFillableEqualExpected()
    {
        $expected = ['name', 'type'];
        $this->assertEquals($expected, $this->castMember->getFillable());
    }

    public function testIsAttributeCastsEqualExpected()
    {
        $expected = [
            'id' => 'string',
        ];
        $this->assertEquals($expected, $this->castMember->getCasts());
    }

    public function testIsAttributeDatesEqualExpected()
    {
        $expected = ['created_at', 'deleted_at', 'updated_at'];
        $this->assertEqualsCanonicalizing($expected, $this->castMember->getDates());
        $this->assertCount(count($expected), $this->castMember->getDates());
    }

    public function testIsAttributeIncrementingEqualFalse()
    {
        $this->assertFalse($this->castMember->getIncrementing());
    }

    public function testIfUsesExpectedTraits()
    {
        $expected = [
            SoftDeletes::class,
            Uuid::class,
        ];

        $traitsInCastMember = array_keys(class_uses(castMember::class));
        $this->assertEquals($expected, $traitsInCastMember);
    }
}
