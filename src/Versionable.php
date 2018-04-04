<?php
namespace Sasin91\LaravelVersionable;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection as DatabaseCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Sasin91\LaravelVersionable\CreateVersionJob;
use Sasin91\LaravelVersionable\VersionableModelObserver;

trait Versionable
{
    use 
        Concerns\Revertable,
        Concerns\Ressurectable;

    /**
     * Boot Versioning trait.
     *
     * @return void
     */
    public static function bootVersionable()
    {
        static::observe(new VersionableModelObserver);
        (new static)->registerVersioningMacros();
    }

    /**
     * Register the Versioning macros.
     *
     * @return void
     */
    public function registerVersioningMacros()
    {
        $this->registerReversionMacros();

        Collection::macro('versionable', function () {
            if (config('versionable.queue')) {
                return $this->map->queueCreateVersion();
            }

            return $this->map->createVersion();
        });
    }

    /**
     * Temporarily disable Versioning during given callback.
     *
     * @param callable $callback
     */
    public static function withoutVersionining($callback)
    {
        static::disableVersioning();
        try {
            return $callback();
        } finally {
            static::enableVersioning();
        }
    }

    /**
     * Enable Versioning for the current model
     */
    public static function enableVersioning()
    {
        VersionableModelObserver::enableVersionableFor(get_called_class());
    }

    /**
     * Disable Versioning for the current model
     */
    public static function disableVersioning()
    {
        VersionableModelObserver::disableVersionableFor(get_called_class());
    }

    /**
     * Create a Version of the current Model(s).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function versionable()
    {
        return DatabaseCollection::make([$this])->versionable();
    }
    /**
     * Versions Relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function versions()
    {
        return $this->morphMany(Version::class, 'versionable');
    }

    /**
     * Get the latest version
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function latestVersion()
    {
        return $this->morphOne(Version::class, 'versionable')->latest('id');
    }

    /**
     * Get the next to latest version
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function previousVersion()
    {
        return $this->latestVersion()->skip(1);
    }

    /**
     * Get the oldest version
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne 
     */
    public function oldestVersion()
    {
        return $this->morphOne(Version::class, 'versionable')->oldest('id');  
    }

    /**
     * Dispatch a queued job for creating this version.
     * 
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function queueCreateVersion()
    {
        return dispatch(new CreateVersionJob($this));
    }

    /**
     * Create a Version record of the current Model.
     *
     * @return \App\Version
     */
    public function createVersion()
    {
        return $this->versions()->create([
            'user_id' => optional($this->versionableUser())->id,
            'attributes' => $this->toVersionableArray()
        ]);
    }

    /**
     * Make a Version of the current Model.
     *
     * @return \App\Version
     */
    public function makeVersion()
    {
        return $this->versions()->make([
            'user_id' => optional($this->versionableUser())->id,
            'attributes' => $this->toVersionableArray()
        ]);
    }

    public function versionableUser()
    {
        if ($this instanceof Authenticatable) {
            return $this;
        }

        return $this->user ?? Auth::user();
    }

    /**
     * Get the versionable data array for the model.
     *
     * @return array
     */
    public function toVersionableArray()
    {
        return $this->toArray();
    }
}