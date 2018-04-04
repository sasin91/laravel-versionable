<?php

namespace Sasin91\LaravelVersionable\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Sasin91\LaravelVersionable\CreateVersionJob;
use Sasin91\LaravelVersionable\Tests\TestCase;
use Sasin91\LaravelVersionable\Tests\VersionableModel;

class RessurectingModelsTest extends TestCase
{
	/** @test */
	function can_ressurect_a_destroyed_model_from_a_version() 
	{
		config(['versionable.ressurection' => true]);

		$model = VersionableModel::create(['name' => 'John doe']);

		$version = $model->latestVersion;

		$model->forceDelete();

		$this->assertDatabaseMissing('__testing_versionables', [
			'name' => 'John doe'
		]);

		$ressurected = $version->ressurect();
		$this->assertEquals('John doe', $ressurected->name);
		$this->assertDatabaseHas('__testing_versionables', [
			'name' => 'John doe'
		]);
	}

	/** @test */
	function ressurecting_an_existing_model_does_nothing() 
	{
		config(['versionable.ressurection' => true]);

		$model = VersionableModel::create(['name' => 'John doe']);

		$version = $model->latestVersion;

		DB::enableQueryLog();
		$ressurected = $version->ressurect();
		$this->assertCount(1, DB::getQueryLog(), 'Additional unexpected queries was performed.');

		$this->assertEquals('John doe', $ressurected->name);
		$this->assertDatabaseHas('__testing_versionables', [
			'name' => 'John doe'
		]);
	} 
}