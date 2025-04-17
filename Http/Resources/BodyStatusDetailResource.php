<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BodyStatusDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
             'id' => optional($this->section)->id,
            'title' => optional($this->section)->name,
            'value' => isset($this->result)?(string) $this->result->value:'0.0',
            'image' => optional($this->section)->getFirstMediaUrl('images'),
            'logo' => optional($this->section)->getFirstMediaUrl('icons'),
            'status' => $this->result->status??'empty',
            'period' => ($this->updated_at->diffForHumans() != $this->created_at->diffForHumans())?$this->updated_at->diffForHumans():'--',
            'risk_details' => $this->result && $this->result->risk ? $this->result->risk->details : null,
            'risk_date' => $request->updated_at ? Carbon::parse($request->updated_at)->format('Y-m-d') : null,
            'risk_title' => $this->result && $this->result->risk ? $this->result->risk->title : null,
            'upcoming_risks' => $this->result && $this->result->risk ? $this->result->risk->upcoming_risks : null,
            'health_dynamics' => $this->health_dynamics,


            // age_statistics => string
            // age_statistics_url => string (url)
            'known_issues' => isset($this->result)?$this->result->known_issue:null,
            // latest_vital_signs_date => string
            // latest_vital_signs => 
            // normal => num
            // abnormal => num
            // old => num
            // uploaded_tests => list of image urls
            // current_tests => list model
            // id => num
            // title => string
            // percentage => string (nullable)
            // date => string (nullable)
            // diagram_url => string (nullable)
            // old_tests => list of model (same as current)
            // empty_test => list of model (same as current)
            // common_details
            // common_empty_tests => list of models (same as current)
            'related_topics' => TopicResource::collection($this->Section->Articles),
        ];
    }
}
