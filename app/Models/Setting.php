<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Récupère une valeur (décodée depuis le JSON si applicable).
     */
    public static function get(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();

        if (! $row) {
            return $default;
        }

        $decoded = json_decode($row->value, true);

        return $decoded === null ? $row->value : $decoded;
    }

    /**
     * Crée ou met à jour une valeur (encodée en JSON si tableau/objet).
     */
    public static function set(string $key, $value): void
    {
        $encoded = (is_array($value) || is_object($value)) ? json_encode($value) : $value;

        static::updateOrCreate(['key' => $key], ['value' => $encoded]);
    }
}
