<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;

trait HasUuid
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function($model) {
            if (!isset($model->uuid)) {
                $model->uuid = Uuid::uuid4()->toString();
            }

            return true;
        });
    }
}
