<?php

namespace App\Http\Resources;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Branch */
class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $location = $this->location();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'commune' => $this->commune,
            'region' => $this->region,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'openingHours' => $this->opening_hours,
            'href' => route('branches.show', $this->resource, absolute: false),
            'phoneUrl' => $this->phone ? 'tel:+'.$this->digitsOnly($this->phone) : null,
            'whatsappUrl' => $this->whatsapp ? 'https://wa.me/'.$this->digitsOnly($this->whatsapp) : null,
            'directionsUrl' => 'https://www.google.com/maps/dir/?'.http_build_query(
                ['api' => 1, 'destination' => $location],
                encoding_type: PHP_QUERY_RFC3986,
            ),
            'mapEmbedUrl' => 'https://www.google.com/maps?'.http_build_query(
                ['q' => $location, 'output' => 'embed'],
                encoding_type: PHP_QUERY_RFC3986,
            ),
        ];
    }

    private function location(): string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return "{$this->latitude},{$this->longitude}";
        }

        return "{$this->address}, {$this->commune}, {$this->region}, Chile";
    }

    private function digitsOnly(string $value): string
    {
        return (string) preg_replace('/\D+/', '', $value);
    }
}
