<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiturStore extends Model
{
    protected $table = 'mst_fitur_store';

    protected $fillable = ['konten', 'status'];

    protected $casts = [
        'konten' => 'array',
        'status' => 'boolean',
    ];

    public function getFeatureItemsAttribute(): array
    {
        $data = data_get($this->konten, 'data', []);

        if (is_array($data) && ! empty($data)) {
            return array_values(array_filter($data, fn ($value) => trim((string) $value) !== ''));
        }

        $fallback = array_filter([
            data_get($this->konten, 'title'),
            data_get($this->konten, 'description'),
        ], fn ($value) => trim((string) $value) !== '');

        return array_values($fallback);
    }
}
