<?php

namespace Sasin91\LaravelVersionable\Unit;

use Sasin91\LaravelVersionable\Tests\TestCase;
use Sasin91\LaravelVersionable\Tests\VersionableModel;

class ScoopingVersionsTest extends TestCase
{
	/** @test */
	function can_scoop_versions_between_two_dates() 
	{
		$model = VersionableModel::create(['name' => 'John Doe']);
		$model->createVersion();		
		$this->assertEquals(2, $model->versions()->count());

		tap($model->versions, function ($versions) {
			$versions->first()->forceFill(['created_at' => '2018-03-29'])->saveOrFail();
			$versions->last()->forceFill(['created_at' => '2018-04-01'])->saveOrFail();
		});

		$this->assertEquals(2, $model->versions()->between('2018-03-28', '2018-04-01')->count());
	}

	/** @test */
	function can_scoop_versions_before_a_given_date() 
	{
		$model = VersionableModel::create(['name' => 'John Doe']);
		$model->latestVersion->forceFill(['created_at' => '2018-04-01'])->saveOrFail();

		$model->createVersion()->forceFill(['created_at' => '2018-03-30'])->saveOrFail();

		$versions = $model->versions()->before('2018-04-01')->get();

		$this->assertCount(1, $versions);
		$this->assertTrue($versions->first()->getVersionedModel()->is($model));
	}

	/** @test */
	function can_scoop_versions_at_a_given_date() 
	{
		$model = VersionableModel::create(['name' => 'John Doe']);
		
		$model->createVersion()->forceFill(['created_at' => '2018-03-30'])->saveOrFail();

		$versions = $model->versions()->at('2018-03-30')->get();

		$this->assertCount(1, $versions);
		$this->assertTrue($versions->first()->getVersionedModel()->is($model));
	} 
}