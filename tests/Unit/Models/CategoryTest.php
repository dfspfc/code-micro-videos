<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;
use App\Models\Traits\Uuid;

class CategoryTest extends TestCase
{
    private $category;

    protected function setUp() : void {
        parent::setUp();
        $this->category = new Category();
    }    

    public function testIsAttributeFillableEqualExpected()
    {
        $expected = ['name', 'description', 'is_active'];
        $this->assertEquals($expected, $this->category->getFillable());
    }

    public function testIsAttributeCastsEqualExpected()
    {
        $expected = [
            'id' => 'string',
            'is_active' => 'boolean',
        ];
        $this->assertEquals($expected, $this->category->getCasts());
    }

    public function testIsAttributeDatesEqualExpected()
    {
        $expected = ['created_at', 'deleted_at', 'updated_at'];
        $this->assertEqualsCanonicalizing($expected, $this->category->getDates());
        $this->assertCount(count($expected), $this->category->getDates());
    }

    public function testIsAttributeIncrementingEqualFalse()
    {
        $this->assertFalse($this->category->getIncrementing());
    }

    public function testIfUsesExpectedTraits()
    {
        $expected = [
            SoftDeletes::class,
            Uuid::class,
        ];

        $traitsInCategory = array_keys(class_uses(Category::class));
        $this->assertEquals($expected, $traitsInCategory);
    }
}
