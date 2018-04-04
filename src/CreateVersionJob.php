<?php

namespace Sasin91\LaravelVersionable;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateVersionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The model we're creating a version of.
     *     
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;

        $queue = config('versionable.queue');
        if (is_string($queue) && filled($queue)) {
            $this->onQueue($queue);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    	$this->model->createVersion();
    }
}
