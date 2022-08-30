<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\SubmissionFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pandoc\Facades\Pandoc;

class ProcessContentUpload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\SubmissionFile $file Submission File to process
     * @return void
     */
    public function __construct(SubmissionFile $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @param \Pandoc\Facades\Pandoc $pandoc Inject the pandoc class
     * @return void
     */
    public function handle(Pandoc $pandoc)
    {
        $this->file
    }
}
