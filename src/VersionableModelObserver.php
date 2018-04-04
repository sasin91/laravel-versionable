<?php

namespace Sasin91\LaravelVersionable;

use Carbon\CarbonInterval;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sasin91\LaravelVersionable\Version;

class VersionableModelObserver
{
    /**
     * Versionable Models with Versionable disabled.
     *
     * @var array
     */
    public static $versionableDisabledFor = [];

    /**
     * Disable versioning for given model.
     *
     * @param  string|object $model
     * @return void
     */
    public static function disableVersionableFor($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        static::$versionableDisabledFor[$model] = true;
    }

    /**
     * Enable versioning for given Model.
     *
     * @param  string|object $model
     * @return void
     */
    public static function enableVersionableFor($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        unset(static::$versionableDisabledFor[$model]);
    }

    /**
     * Determine if versioning is disabled for given Model.
     *
     * @param  string|object $model
     * @return bool
     */
    public static function versionableDisabledFor($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        return isset(static::$versionableDisabledFor[$model]);
    }

    /**
     * Handle the created model event.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function created($model)
    {
        if (config('versionable.original') && $this->shouldVersion($model)) {
            $model->versionable();
        }
    }

    /**
     * Handle the updated model event.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function updated($model)
    {
        if ($this->shouldVersion($model)) {
            $model->versionable();
        }
    }

    /**
     * Soft delete the versions when the parent is deleted.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function deleted($model)
    {
        if ($this->isForceDeleting($model)) {
            if (! config('versionable.ressurection')) {
                $model->versions->each->forceDelete();

                return;
            }

            if (is_string(config('versionable.ressurection'))) {
                $this->scheduleRessurectionExpiration($model);
            }
        }

        $model->versions->each->delete();
    }

    /**
     * Schedule destroying the ressurectable versions for good.
     * 
     * @param  \Illuminate\Database\Eloquent\Model $model 
     * @return void        
     */
    protected function scheduleRessurectionExpiration($model)
    {
        $versionIds = $model->versions->pluck('id');
        $interval = CarbonInterval::fromString(config('versionable.ressurection'));
        $date = now()->add($interval);

        $minutes = $date->format('i');
        $hours = $date->format('H');
        $day = $date->format('d') ?: '*';
        $month = $date->format('m') ?: '*';
        $weekday = $date->format('w') ?: '*';

        $e = resolve(Schedule::class)->call(function () use ($versionIds) {
          return Version::withoutGlobalScopes()->whereIn('id', $versionIds)->forceDelete();
        })->cron("{$minutes} {$hours} {$day} {$month} {$weekday}");
    }

    /**
     * Determine whether we're soft deleting the given model
     *     
     * @param  \Illuminate\Database\Eloquent\Model  $model 
     * @return boolean        
     */
    protected function isForceDeleting($model)
    {
        // in_array(SoftDeletes::class, class_uses_recursive($model))
        if (method_exists($model, 'isForceDeleting')) {
            return $model->isForceDeleting();
        }

        return true;
    }

    /**
     * Determine if we should version the model.
     * 
     * @param  \Illuminate\Database\Eloquent\Model $model 
     * @return bool        
     */
    protected function shouldVersion($model)
    {
        if (static::versionableDisabledFor($model)) {
            return false;
        }

        return $this->hasVersionableChanges($model);
    }

    /**
     * Determine if the model contains any versionable changes.
     * 
     * @param  \Illuminate\Database\Eloquent\Model  $model 
     * @return boolean        
     */
    protected function hasVersionableChanges($model)
    {
        $versionable = $model->toVersionableArray();

        return collect($model->getDirty())->keys()->filter(function ($key) use ($versionable) {
            return array_key_exists($key, $versionable);
        })->isNotEmpty();
    }
}