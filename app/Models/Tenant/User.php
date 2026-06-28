<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable
{
    use HasUuids, Notifiable, HasRoles, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function sendPasswordResetNotification($token): void
    {
        \Illuminate\Auth\Notifications\ResetPassword::createUrlUsing(function ($notifiable, $token) {
            return request()->getSchemeAndHttpHost() . '/reset-password/' . $token
                . '?' . http_build_query(['email' => $notifiable->getEmailForPasswordReset()]);
        });

        $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
    }

    public function linkedChildren(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'user_id', 'student_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }
}
