<?php

namespace Sasin91\LaravelVersionable\Tests\Unit;

use Sasin91\LaravelVersionable\Tests\TestCase;
use Sasin91\LaravelVersionable\Tests\VersionableModel;
use Sasin91\LaravelVersionable\VersionableModelObserver;

class TogglingVersioningTest extends TestCase
{
	/** @test */
	function can_disable_versioning() 
	{
		VersionableModel::disableVersioning();

		$this->assertTrue(VersionableModelObserver::versionableDisabledFor(VersionableModel::class), 'Versioning did not get disabled.');
	} 

	/** @test */
	function can_enable_versioning() 
	{
		VersionableModel::disableVersioning();
		VersionableModel::enableVersioning();

		$this->assertFalse(VersionableModelObserver::versionableDisabledFor(VersionableModel::class), 'Versioning did not get enabled.');
	} 

	/** @test */
	function can_temporarily_disable_versioning() 
	{
		$this->assertFalse(VersionableModelObserver::versionableDisabledFor(VersionableModel::class));

		VersionableModel::withoutVersionining(function () {
			$this->assertTrue(VersionableModelObserver::versionableDisabledFor(VersionableModel::class));
		});

		$this->assertFalse(VersionableModelObserver::versionableDisabledFor(VersionableModel::class));
	} 
}