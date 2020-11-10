<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use Illuminate\Foundation\Testing\TestResponse;

trait JsonFragmentValidation
{
    protected function assertRequired(TestResponse $response, $attribute)
    {
        $this->assertInvalidJsonFragment($response, $attribute, 'required');
    }

    protected function assertMax255(TestResponse $response, $attribute)
    {
        $this->assertInvalidJsonFragment($response, $attribute, 'max.string', ['max' => 255]);
    }

    protected function assertYear(TestResponse $response, $attribute)
    {
        $this->assertInvalidJsonFragment($response, $attribute, 'date_format', ['format' => 'Y']);
    }

    protected function assertNotInDatabase(TestResponse $response, $attribute)
    {
        $this->assertInvalidJsonFragment($response, $attribute, 'exists');
    }

    protected function assertBoolean(TestResponse $response, $attribute) {
        $this->assertInvalidJsonFragment($response, $attribute, 'boolean');
    }

    protected function assertInteger(TestResponse $response, $attribute) {
        $this->assertInvalidJsonFragment($response, $attribute, 'integer');
    }

    protected function assertArray(TestResponse $response, $attribute) {
        $this->assertInvalidJsonFragment($response, $attribute, 'array');
    }

    protected function assertNotIn(TestResponse $response, $attribute) {        
        $this->assertInvalidJsonFragment($response, $attribute, 'in');
    }

    protected function assertInvalidJsonFragment(
        TestResponse $response,
        $attribute,
        $rule,
        $ruleConfig = []
    ) {
        $response
            ->assertJsonValidationErrors([$attribute])
            ->assertJsonFragment([
                Lang::get(
                    'validation.' . $rule,
                    ['attribute' => $this->withoutUnderscore($attribute)] + $ruleConfig
                )
            ]);
    }

    private function withoutUnderscore($attribute)
    {
        return str_replace('_', ' ', $attribute);
    }
}
