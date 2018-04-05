<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVersionsTable extends Migration
{
	public function up()
	{
		Schema::create('versions', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
            
            $table->nullableMorphs('versionable');
            $table->json('attributes')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('reverted_at')->nullable();
            $table->timestamp('ressurected_at')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('versions');
	}
}