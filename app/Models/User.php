<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Rules\Password;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\Jetstream;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the validation array.
     * 
     * @return array
     */
    public static function getValidation(self $user = null): array
    {
        $validation = [
            'first_name' => ['required', 'string', 'max:20'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [empty($user) ? 'required' : 'nullable', 'string', new Password, 'confirmed'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];

        if (empty($user)) {
            $validation['terms'] = Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '';
        }

        return $validation;
    }

    /**
     * Create a new model in the database.
     * 
     * @param  array  $attributes
     * @param  \App\Models\User  $user
     * @return \App\Models\User
     */
    public static function create(array $attributes, bool $validate = true): self
    {
        if ($validate) {
            Validator::make($attributes, self::getValidation())->validate();
        }

        $user = new self;
        $user->fill($attributes);
        $user->password = Hash::make($attributes['password']);
        $user->save();

        return $user;
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function update(array $attributes = [], array $options = [], self $user = null, bool $validate = true): bool
    {
        if ($validate) {
            Validator::make($attributes, self::getValidation($this))->validate();
        }

        if ($attributes['email'] !== $this->email && $this instanceof MustVerifyEmail) {
            $this->email_verified_at = null;
            $this->email = $attributes['email'];

            $this->sendEmailVerificationNotification();
        }

        $this->fill($attributes);

        if (isset($attributes['photo'])) {
            $this->updateProfilePhoto($attributes['photo']);
        }

        return $this->save();
    }
}
