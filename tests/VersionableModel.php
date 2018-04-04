<?php

namespace Sasin91\LaravelVersionable\Tests;

use Illuminate\Database\Eloquent\Model;
use Sasin91\LaravelVersionable\Versionable;

class VersionableModel extends Model
{
	use Versionable;

	protected $connection = 'testbench';

	protected $table = '__testing_versionables';

	protected $guarded = [];
}