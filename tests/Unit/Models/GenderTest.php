<?php

namespace Tests\Unit\Models;

use App\Models\Gender;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use App\Models\Traits\Uuid;

class GenderTest extends TestCase
{
    private $gender;

    protected function setUp() : void {
        parent::setUp();
        $this->gender = new Gender();
    }    

    public function testIsAttributeFillableEqualExpected()
    {
        $expected = ['name', 'is_active'];
        $this->assertEquals($expected, $this->gender->getFillable());
    }

    public function testIsAttributeCastsEqualExpected()
    {
        $expected = [
            'id' => 'string',
            'is_active' => 'boolean',
        ];
        $this->assertEquals($expected, $this->gender->getCasts());
    }

    public function testIsAttributeDatesEqualExpected()
    {
        $expected = ['created_at', 'deleted_at', 'updated_at'];
        $this->assertEqualsCanonicalizing($expected, $this->gender->getDates());
        $this->assertCount(count($expected), $this->gender->getDates());
    }

    public function testIsAttributeIncrementingEqualFalse()
    {
        $this->assertFalse($this->gender->getIncrementing());
    }

    public function testIfUsesExpectedTraits()
    {
        $expected = [
            SoftDeletes::class,
            Uuid::class,
        ];

        $traitsInGender = array_keys(class_uses(gender::class));
        $this->assertEquals($expected, $traitsInGender);
    }
}
