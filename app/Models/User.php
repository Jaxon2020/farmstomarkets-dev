<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id', 'supabase_id', 'email', 'name', 'password', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Create a new User instance from Supabase data.
     *
     * @param \stdClass $data
     * @return self
     */
    public static function fromSupabase(\stdClass $data): self
    {
        // Validate required fields
        if (!isset($data->id) || !isset($data->email)) {
            throw new \Exception('Supabase user data is missing required fields: id or email');
        }

        return self::firstOrCreate(
            ['supabase_id' => $data->id], // Match by Supabase user ID
            [
                'id' => $data->id, // Use Supabase ID as the primary key
                'email' => $data->email,
                'name' => $data->user_metadata->name ?? 'Guest', // Default to 'Guest' if name is not set
                'password' => bcrypt(Str::random(16)), // Generate a random password since Supabase handles auth
                'created_at' => $data->created_at ?? now(),
                'updated_at' => $data->updated_at ?? now(),
            ]
        );
    }
}