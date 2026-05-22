<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "created_at" => $this->when(
                $request->is("api/tags*"),
                $this->created_at,
            ),
            "updated_at" => $this->when(
                $request->is("api/tags*"),
                $this->updated_at,
            ),
        ];
    }
}
