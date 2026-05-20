<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApprovalActionCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ApprovalRequestResource::class;

    /**
     * The type of approval action (pending, upcoming, ongoing, history).
     *
     * @var string
     */
    protected string $type;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  array  $counts
     * @param  string  $type
     * @return void
     */
    public function __construct($resource, array $counts = [], string $type = 'pending')
    {
        parent::__construct($resource);
        $this->counts = $counts;
        $this->type = $type;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'summary_counts' => $this->counts,
            'active_tab' => $this->type,
        ];
    }
}
