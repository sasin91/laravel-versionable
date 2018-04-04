<?php

namespace Sasin91\LaravelVersionable\Tests\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestTables extends Migration
{
	public function up()
	{
		Schema::create('__testing_versionables', function ($table) {
			$table->increments('id');

			$table->string('name')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('__testing_versionables');
	}
}