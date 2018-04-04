<?php

namespace Sasin91\LaravelVersionable\Tests;

include(__DIR__.'/../migrations/create_versions_table.php');

use CreateVersionsTable;
use Orchestra\Testbench\TestCase as TestBench;
use Sasin91\LaravelVersionable\VersionableServiceProvider;

class TestCase extends TestBench
{
	 protected function setUp()
	 {
	 	parent::setUp();

//	 	$this->artisan('migrate', [
//		    '--database' => 'testbench',
//		    '--realpath' => realpath(__DIR__.'/../migrations'),
//		]);

	 	(new \CreateVersionsTable)->up();
	 	(new Migrations\CreateTestTables)->up();
	 }

	 protected function tearDown()
	 {
	 	(new \CreateVersionsTable)->down();
	 	(new Migrations\CreateTestTables)->down();

	 	parent::tearDown();
	 }

	protected function getPackageProviders($app)
	{
		return [
			VersionableServiceProvider::class
		];
	}
	protected function getPackageAliases($app)
	{
		return [
			//
		];
	}
	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application
	 *
	 * @return void
	 */
	protected function getEnvironmentSetUp($app)
	{
		// Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => '',
		]);
	}
}