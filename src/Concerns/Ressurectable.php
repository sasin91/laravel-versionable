<?php

namespace Sasin91\LaravelVersionable\Concerns;

use Illuminate\Support\Collection;

trait Ressurectable
{
	/**
	 * Register the macros for ressurecting a versioned model
	 * 
	 * @return void
	 */
	public function registerRessurectionMacros()
	{
        Collection::macro('ressurect', function () {
            return $this->map->ressurect();
        });
	}

    /**
     * Register a ressurecting model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function ressurecting($callback)
    {
        static::registerModelEvent('ressurecting', $callback);
    }

    /**
     * Register a ressurected model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function ressurected($callback)
    {
        static::registerModelEvent('ressurected', $callback);
    }

    /**
     * Ressurect a model from a version of it.
     *
     * @param  string|object $version
     *
     * @return \App\Version | bool
     */
    public function ressurect($version = null)
    {
        // Like the pheonix, rise! rise from the ashes..
        if ($this->fireModelEvent('ressurecting') === false) {
        	// oh shit, gotta bail. Cya!
            return false;
        }

        return tap($this->version($version), function (&$version) {
            if ($version) {
                $version = $version->ressurect();
                
                $this->fireModelEvent('ressurected', false);
            }
        });
    }
}