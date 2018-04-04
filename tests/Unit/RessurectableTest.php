<?php

namespace Sasin91\LaravelVersionable\Tests\Unit;

use Carbon\CarbonInterval;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Sasin91\LaravelVersionable\CreateVersionJob;
use Sasin91\LaravelVersionable\Tests\TestCase;
use Sasin91\LaravelVersionable\Tests\VersionableModel;

class RessurectableTest extends TestCase
{
	/** @test */
	function it_destroys_versions_when_ressurection_is_not_enabled() 
	{
		config(['versionable.ressurection' => false]);

		$model = VersionableModel::create();

		$model->createVersion();

		$this->assertNotEmpty($model->versions);
		$model->delete();

		$this->assertDatabaseMissing('versions', [
			'versionable_type' => VersionableModel::class,
			'versionable_id' => $model->id
		]);
	}

	/** @test */
	function can_define_a_ressurection_lifetime() 
	{	
		$this->markTestIncomplete('
			Cannot figure out how to properly test scheduling a cron job to delete versions when ressurection time expires...
		');

		// config(['versionable.ressurection' => '30d']);

		// $model = VersionableModel::create();

		// $model->createVersion();

		// $model->delete();
		// eloquent.deleted: model -> VersionableModelObserver@deleted -> scheduleRessurectionExpiration
		// Assert cron job was added to forceDelete the models version(s)...
	} 

	/** @test */
	function it_soft_deletes_versions_when_parent_has_been_soft_deleted() 
	{
		config(['versionable.ressurection' => true]);

		$model = VersionableModel::create();

		$model->createVersion();

		$this->assertNotEmpty($model->versions);
		$model->delete();

		$this->assertSoftDeleted('versions', [
			'versionable_type' => VersionableModel::class,
			'versionable_id' => $model->id
		]);
	} 

	/** @test */
	function it_retains_versions_when_ressurection_is_enabled() 
	{
		config(['versionable.ressurection' => true]);

		$model = VersionableModel::create();

		$model->createVersion();

		$this->assertNotEmpty($model->versions);
		$model->forceDelete();

		$this->assertSoftDeleted('versions', [
			'versionable_type' => VersionableModel::class,
			'versionable_id' => $model->id
		]);
	} 
}