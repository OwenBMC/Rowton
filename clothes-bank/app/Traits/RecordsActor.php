<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait RecordsActor
{
    /**
     * Boot the trait and automatically set actor_type and actor_id on creation.
     */
    protected static function bootRecordsActor(): void
    {
        static::creating(function (Model $model) {
            // Only set automatically if not explicitly provided
            if (empty($model->actor_type) && empty($model->actor_id)) {
                $actor = app('current_actor');

                if ($actor) {
                    $model->actor_type = get_class($actor);
                    $model->actor_id = $actor->id;
                }
            }
        });
    }

    /**
     * Polymorphic relation back to the actor (Staff or Volunteer).
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}