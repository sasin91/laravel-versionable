<?php

namespace Sasin91\LaravelVersionable\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Sasin91\LaravelVersionable\CreateVersionJob;
use Sasin91\LaravelVersionable\Tests\TestCase;
use Sasin91\LaravelVersionable\Tests\VersionableModel;

class VersioningModelsTest extends TestCase
{
	/** @test */
	function it_automatically_versions_models() 
	{
		 $model = VersionableModel::create(['name' => 'john doe']);

		 $model->update(['name' => 'jane doe']);

		 $this->assertCount(2, $model->versions);
		 $this->assertEquals('john doe', $model->previousVersion->getVersionedModel()->name);
		 $this->assertEquals('jane doe', $model->latestVersion->getVersionedModel()->name);
	}

	/** @test */
	function can_manually_create_a_version() 
	{
		$model = VersionableModel::create(['name' => 'john doe']);

		$model->createVersion();

		$this->assertCount(2, $model->versions);
		 $this->assertEquals('john doe', $model->previousVersion->getVersionedModel()->name);
		 $this->assertEquals('john doe', $model->latestVersion->getVersionedModel()->name);
	}

	/** @test */
	function it_dispatches_versioning_to_the_queue_when_enabled() 
	{
		Bus::fake();

		config(['versionable.queue' => true]);

		$model = VersionableModel::create(['name' => 'john doe']);

		Bus::assertDispatched(CreateVersionJob::class, function ($job) use ($model) {
			return $job->model->is($model);
		});
	}

	/** @test */
	function it_dispatches_versioning_to_a_specific_queue() 
	{
		Bus::fake();

		config(['versionable.queue' => 'versioning']);

		$model = VersionableModel::create(['name' => 'john doe']);

		Bus::assertDispatched(CreateVersionJob::class, function ($job) use ($model) {
			return $job->model->is($model)
				&& $job->queue === 'versioning';
		});
	} 
}