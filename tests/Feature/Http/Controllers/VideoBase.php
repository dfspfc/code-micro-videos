<?php

namespace Tests\Feature\Http\Controllers;

trait VideoBase
{
    private function postVideo($bodyRequest = [])
    {
        return $this->json(
            'POST',
            route('videos.store'),
            $bodyRequest,
        );
    }

    private function updateVideo($id, $attributes = [])
    {
        return $this->json(
            'PUT',
            route(
                'videos.update',
                ['video' => $id],
            ),
            $attributes,
        );
    }
}
