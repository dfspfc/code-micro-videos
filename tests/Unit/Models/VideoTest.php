<?php

namespace Tests\Unit\Models;

use App\Models\Video;
use App\Models\Category;
use App\Models\Gender;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;
use App\Models\Traits\Uuid;

class VideoTest extends TestCase
{
    private $video;

    protected function setUp() : void {
        parent::setUp();
        $this->video = new Video();
    }    

    public function testIsAttributeFillableEqualExpected()
    {
        $expected = [
            'title', 
            'description',
            'year_launched', 
            'opened',
            'rating', 
            'duration',
        ];
        $this->assertEquals($expected, $this->video->getFillable());
    }

    public function testIsAttributeCastsEqualExpected()
    {
        $expected = [
            'id' => 'string',
            'opened' => 'boolean',
            'year_launched' => 'integer',
            'duration' => 'integer',
        ];
        $this->assertEquals($expected, $this->video->getCasts());
    }

    public function testIsAttributeDatesEqualExpected()
    {
        $expected = ['created_at', 'deleted_at', 'updated_at'];
        $this->assertEqualsCanonicalizing($expected, $this->video->getDates());
        $this->assertCount(count($expected), $this->video->getDates());
    }

    public function testIsAttributeIncrementingEqualFalse()
    {
        $this->assertFalse($this->video->getIncrementing());
    }

    public function testIfUsesExpectedTraits()
    {
        $expected = [
            SoftDeletes::class,
            Uuid::class,
        ];

        $traitsInVideo = array_keys(class_uses(video::class));
        $this->assertEquals($expected, $traitsInVideo);
    }
}
