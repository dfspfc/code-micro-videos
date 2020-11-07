<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;

class BaseApiControllerTest extends TestCase
{
    const CATEGORY_DATA_SAMPLE = [
        'name' => 'test name',
        'description' => 'test description',
    ];
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {

        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndexShouldGetAllObjects()
    {
        $expectedCategoryStub = CategoryStub::create(SELF::CATEGORY_DATA_SAMPLE)
            ->toArray();
                
        $this->assertEquals(
            [$expectedCategoryStub],
            $this->controller->index()->toArray()
        );
    }

    public function testValidationFailingShouldThrowAnException()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $mockRequest = Mockery::mock(Request::class);
        $mockRequest
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);

        $this->controller->store($mockRequest);
    }

    public function testShowShouldGetSpecificObject()
    {
        $categoryStub = CategoryStub::create(SELF::CATEGORY_DATA_SAMPLE);
        $result = $this->controller->show($categoryStub->id);
        $this->assertEquals(
            $result->toArray(),
            CategoryStub::find($categoryStub->id)->toArray()
        );
    }

    public function testStoreShouldCreateAnObject()
    {
        $sampleData = [
            'name' => 'unique name to be asserted'
        ];
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest
            ->shouldReceive('all')
            ->once()
            ->andReturn($sampleData);
        $result = $this->controller->store($mockRequest)->toArray();

        $this->assertEquals($sampleData['name'], $result['name']);
    }

    public function testUpdateShouldUpdateObject()
    {
        $categoryStub = CategoryStub::create(SELF::CATEGORY_DATA_SAMPLE);
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest
            ->shouldReceive('all')
            ->once()
            ->andReturn(SELF::CATEGORY_DATA_SAMPLE);
        $result = $this->controller->update($mockRequest, $categoryStub->id);

        $this->assertEquals(
            $result->toArray(),
            CategoryStub::find($categoryStub->id)->toArray()
        );
    }

    public function testDestroyShouldDeleteObject()
    {
        $categoryStub = CategoryStub::create(SELF::CATEGORY_DATA_SAMPLE);
        $response = $this->controller->destroy($categoryStub->id);
        
        $this->createTestResponse($response)
            ->assertStatus(204);
        $this->assertCount(0, CategoryStub::all());
    }

    public function testFindObjectFromModelShouldReturnTheExpectedObjectwithTheModelTypeWhenIdentifierIsValid()
    {
        $categoryStub = CategoryStub::create(SELF::CATEGORY_DATA_SAMPLE);
        $reflectionClass = new \ReflectionClass(BaseApiController::class);
        $methodToBeTested = $reflectionClass->getMethod('findObjectFromModel');
        $methodToBeTested->setAccessible(true);
        $expectedObject = $methodToBeTested->invokeArgs($this->controller, [$categoryStub->id]);

        $this->assertInstanceOf(CategoryStub::class, $expectedObject);
    }

    public function testFindObjectFromModelShouldThrowAnExceptionWhenIdentifierIsInvalid()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $reflectionClass = new \ReflectionClass(BaseApiController::class);
        $methodToBeTested = $reflectionClass->getMethod('findObjectFromModel');
        $methodToBeTested->setAccessible(true);
        $methodToBeTested->invokeArgs($this->controller, ['']);
    }
}
