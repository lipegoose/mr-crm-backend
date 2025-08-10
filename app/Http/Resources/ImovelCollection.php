<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ImovelCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ImovelResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'pagination' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage(),
                'has_more_pages' => $this->resource->hasMorePages(),
            ],
        ];
    }
}
