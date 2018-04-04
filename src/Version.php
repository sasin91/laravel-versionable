<?php

namespace Sasin91\LaravelVersionable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sasin91\LaravelVersionable\Tests\VersionableModel;
use Sasin91\LaravelVersionable\Versionable;

class Version extends Model
{
    use SoftDeletes;

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'versionable_id',
        'versionable_type',
        'user_id',
        'user',
        'attributes',
        'reverted_at',
        'ressurected_at'
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'attributes' => 'array',
        'reverted_at' => 'datetime',
        'ressurected_at' => 'datetime'
    ];

    /**
     * Scope versions of a given model.
     *
     * @param Builder $query
     * @param string | Model $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOf($query, $model)
    {
        if (is_string($model)) {
            $type = Relation::getMorphedModel($model) ?? $model;
        } else {
            $type = get_class($model);
        }

        return $query->where('versionable_type', $type)->when(is_object($model), function (Builder $query) use ($model) {
            $query->where('versionable_id', $model->id);
        });
    }

    /**
     * Eloquent Scope for retrieving Versions at a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder    $query
     * @param  string|\DateTime|Version                 $dateOrVersion
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAt($query, $dateOrVersion)
    {
        if ($dateOrVersion instanceof self) {
            $dateOrVersion = $dateOrVersion->{$dateOrVersion->getCreatedAtColumn()};
        }

        return $query->whereDate($this->getCreatedAtColumn(), '=', $this->asDateTime($dateOrVersion)->format('Y-m-d'));
    }

    /**
     * Eloquent Scope for retrieving Versions after a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder    $query
     * @param  string|\DateTime|Version                 $dateOrVersion
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAfter($query, $dateOrVersion)
    {
        if ($dateOrVersion instanceof self) {
            $dateOrVersion = $dateOrVersion->{$dateOrVersion->getCreatedAtColumn()};
        }

        return $query->whereDate($this->getCreatedAtColumn(), '>', $this->asDateTime($dateOrVersion)->format('Y-m-d'));
    }

    /**
     * Eloquent Scope for retrieving Versions before a specific date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder    $query
     * @param  string|\DateTime|Version                 $dateOrVersion
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBefore($query, $dateOrVersion)
    {
        if ($dateOrVersion instanceof self) {
            $dateOrVersion = $dateOrVersion->{$dateOrVersion->getCreatedAtColumn()};
        }

        return $query->whereDate($this->getCreatedAtColumn(), '<', $this->asDateTime($dateOrVersion)->format('Y-m-d'));
    }

    /**
     * Eloquent Scope for retrieving Versions between two dates date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder        $query
     * @param  string|\DateTime|Version|array               $before
     * @param  string|\DateTime|Version                     $after
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetween($query, $before = null, $after = null)
    {
        if (is_array($before)) {
            [$before, $after] = $before;
        }

        if ($before instanceof self) {
            $before = $before->{$before->getCreatedAtColumn()};
        }

        if ($after instanceof self) {
            $after = $after->{$after->getCreatedAtColumn()};
        }
        
        // Should we handle, if the developer passes nulls ?
        //abort_if((is_null($before) || is_null($after)), $problemExistsBetweenMonitorAndChair = 40);

        return $query
            ->whereDate($this->getCreatedAtColumn(), '>=', $this->asDateTime($before)->format('Y-m-d'))
            ->whereDate($this->getCreatedAtColumn(), '<=', $this->asDateTime($after)->format('Y-m-d'));
    }

    /**
     * The owner of the Version.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dynamically set the user_id attribute.
     *
     * @param mixed $user
     */
    public function setUserAttribute($user)
    {
        if (is_scalar($user)) {
            $user = User::findOrFail($user);
        }

        $this->user()->associate($user);
    }

    /**
     * The versioned model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function versionable()
    {
        return $this->morphTo();
    }

    /**
     * Get the versioned model.
     *     
     * @return Versionable | Model
     */
    public function getVersionedModel()
    {
        return tap($this->versionable, function (Model $model) {
            //$model->exists = true;
            $model->fill($this->getAttribute('attributes'));
        });
    }

    /**
     * Revert to the stored model version
     *
     * @return Model
     */
    public function apply()
    {
        return tap($this->getVersionedModel())->saveOrFail();
    }
    
    /**
     * Get the differences between current versioned or given model.
     * 
     * @param  Version|Versionable $model 
     * @return \Illuminate\Support\Collection        
     */
    public function diff($model = null)
    {
        $model = $model ?? $this->versionable;
        
        $current = $this->getAttribute('attributes');
        $previous = ($model instanceof self) ? $model->getAttribute('attributes') : $model->toVersionableArray();

        return collect($current)
            ->diffAssoc($previous)
            ->keys()
            ->flatMap(function ($key) use ($current, $previous) {
                return [$key => [
                    'current' => $current[$key],
                    'previous' => $previous[$key]
                ]];
            });
    }

    /**
     * Revert the versioned model to the current Version.
     * 
     * @return Versionable|Model
     */
    public function revert()
    {
        return tap($this->apply(), function ($versionedModel) {
            $versionedModel->versions()->after($this)->each->delete();

            $this->update(['reverted_at' => $this->freshTimestamp()]);
        });
    }

    /**
     * Ressurect the versioned model using the current version.
     * 
     * @return Versionable|Model
     */
    public function ressurect()
    {
        if ($this->versionable && $this->versionable->exists) {
            return $this->versionable;
        }

        $this->setRelation('versionable', $this->versionable()->getRelated());

        return tap($this->apply(), function ($versionedModel) {
            $this->update(['ressurected_at' => $this->freshTimestamp()]);
        });        
    }
}