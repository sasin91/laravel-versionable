<?php

namespace Sasin91\LaravelVersionable\Tests\Unit;

use Sasin91\LaravelVersionable\Tests\TestCase;
use Sasin91\LaravelVersionable\Tests\VersionableModel;

class DifferentiationsTest extends TestCase
{
	/** @test */
	function can_diff_against_another_version() 
	{
		$model = VersionableModel::create(['name' => 'John Doe']);

		$model->update(['name' => 'Jane Doe']);

		$diff = $model->latestVersion->diff(
			$model->previousVersion
		);

		$this->assertEquals(['name' => [
			'previous' => 'John Doe',
			'current' => 'Jane Doe'
		]], $diff->toArray());
	} 

	/** @test */
	function it_defaults_to_the_current_versioned_model() 
	{
		$model = VersionableModel::create(['name' => 'John Doe']);
		$version = $model->latestVersion;

		$model->update(['name' => 'Jane Doe']);

		$diff = $version->diff();

		// Unless ordering by dates,
		// it is expected behaviour to see the versions inversed.
		$this->assertEquals(['name' => [
			'current' => 'John Doe',
			'previous' => 'Jane Doe'
		]], $diff->toArray());
	} 
}
