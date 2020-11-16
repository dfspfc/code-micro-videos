<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Database\QueryException;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    public function testMakeRollbackWhenCreationFailsIntheMiddleOfTheTransaction()
    {
        $hasError = false;
        try {
            Video::create([
                'title' => 'title test to be updated',
                'description' => 'description test to be updated',
                'year_launched' => 2008,
                'opened' => true,
                'rating' => 'L',
                'duration' => 20,
                'categories_id' => ['not in database']
            ]);            
        } catch (QueryException $e) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testMakeRollbackWhenUpdateFailsIntheMiddleOfTheTransaction()
    {
        $videoOnDB = factory(Video::class)->create([
                'title' => 'title test to be updated',
                'description' => 'description test to be updated',
                'year_launched' => 2008,
                'opened' => true,
                'rating' => 'L',
                'duration' => 20,
        ]);

        $hasError = false;
        try {
            $videoOnDB->update([
                'title' => 'new title',
                'description' => 'new description',
                'year_launched' => 2009,
                'opened' => false,
                'rating' => '12',
                'duration' => 28,
                'categories_id' => ['not in database']
            ]);
        } catch (QueryException $e) {
            $hasError = true;
            $updatedVideoOnDB = Video::where('id', $videoOnDB->id)
                ->get()
                ->first()
                ->refresh()
                ->toArray();
            $this->assertEquals(
                $videoOnDB->refresh()->toArray(),
                $updatedVideoOnDB
            );
        }
        $this->assertTrue($hasError);
    }
}
