<?php

namespace Sasin91\LaravelVersionable\Concerns;

use Illuminate\Support\Collection;

trait Revertable
{
	/**
	 * Register the macros for reverting a versioned model
	 * 
	 * @return void
	 */
	public function registerReversionMacros()
	{
        Collection::macro('revert', function () {
            return $this->map->revert();
        });
	}

    /**
     * Register a reverting model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function reverting($callback)
    {
        static::registerModelEvent('reverting', $callback);
    }

    /**
     * Register a reverted model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function reverted($callback)
    {
        static::registerModelEvent('reverted', $callback);
    }

    /**
     * Revert to a given version.
     *
     * @param  string|object $version
     *
     * @return \App\Version | bool
     */
    public function revert($version = null)
    {
        // Bail out, on the event that a reverting hook returns false.
        if ($this->fireModelEvent('reverting') === false) {
            return false;
        }
        return tap($this->version($version), function (&$version) {
            if ($version) {
                $version = $version->revert();
                
                $this->fireModelEvent('reverted', false);
            }
        });
    }
}