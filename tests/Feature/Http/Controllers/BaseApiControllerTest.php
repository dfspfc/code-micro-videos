<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;

class CategoryTest extends TestCase
{
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

    public function testIndex()
    {
        $expectedCategoryStub = CategoryStub::create(
            [
                'name' => 'test',
                'description' => ''
            ]
        )->toArray();
                
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

    public function testFindObjectFromModelShouldReturnTheExpectedObjectwithTheModelTypeWhenIdentifierIsValid()
    {
        $categoryStub = CategoryStub::create(
            [
                'name' => 'test name',
                'description' => 'test description',
            ]
        );
        $reflectionClass = new \ReflectionClass(BaseApiController::class);
        $methodToBeTested = $reflectionClass->getMethod('findObjectFromModel');
        $methodToBeTested->setAccessible(true);
        $expectedObject = $methodToBeTested->invokeArgs($this->controller, [$categoryStub->id]);

        $this->assertInstanceOf(CategoryStub::class, $expectedObject);
    }

    public function testFindObjectFromModelShouldThrowAnExceptionWhenIdentifierIsInvalid()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $categoryStub = CategoryStub::create(
            [
                'name' => 'test name',
                'description' => 'test description',
            ]
        );

        $reflectionClass = new \ReflectionClass(BaseApiController::class);
        $methodToBeTested = $reflectionClass->getMethod('findObjectFromModel');
        $methodToBeTested->setAccessible(true);
        $expectedObject = $methodToBeTested->invokeArgs($this->controller, ['']);
    }
}
