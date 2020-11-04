<?php

namespace Tests\Feature\Models;

use App\Models\Gender;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenderTest extends TestCase
{
    use DatabaseMigrations;

    public function testListAllGenderAttributes()
    {
        factory(Gender::class, 1)->create();
        $genders = Gender::all();
        $this->assertCount(1, $genders);
        $genderKey = array_keys($genders->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            $genderKey
        );
    }

    public function testCreatePassingOnlyNameShouldSetTheRestOfTheAtrributesToTheirDefault()
    {
        $expectedName = 'test';
        $gender = Gender::create(['name' => $expectedName]);
        $gender->refresh();

        $this->assertEquals($expectedName, $gender->name);
        $this->assertTrue($gender->is_active);
        $this->assertTrue(Gender::isValid($gender->id));
    }

    public function testCreateWithAttributeIsActiveFalseShouldSetItToFalse()
    {
        $gender = Gender::create(['name' => 'test', 'is_active' => false]);
        $gender->refresh();

        $this->assertFalse($gender->is_active);
    }

    public function testUpdate()
    {
        $gender = Gender::create(['name' => 'test']);

        $updateData = [
            'name' => 'test updated name',
            'is_active' => false,
        ];
        $gender->update($updateData);

        foreach($updateData as $key => $value) {
            $this->assertEquals($value, $gender->{$key});
        }
    }

    public function testDeleteShouldSoftDeleteTheGender()
    {
        $gender = Gender::create(['name' => 'test']);
        $createdGenderId = $gender->id;
        $gender->delete();

        $deletedGender = Gender::where('id', $createdGenderId)
            ->withTrashed()
            ->get()
            ->first();
        
        $this->assertNotNull($deletedGender->deleted_at);
    }
}
