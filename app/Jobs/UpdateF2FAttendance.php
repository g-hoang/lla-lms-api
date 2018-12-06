<?php

namespace App\Jobs;

use App\Models\LearnerUnits;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class UpdateF2FAttendance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $course_id;
    protected $unit_ids = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->course_id = $data['course_id'];
        $this->unit_ids = $data['unit_ids'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $learners = [];

        $learnerUnits = LearnerUnits::where('course_id', $this->course_id)
            ->orderBy('order', 'desc')
            ->get();

        foreach ($learnerUnits as $lu) {
            if (!isset($learners[$lu->learner_id])) {
                foreach ($this->unit_ids as $j => $unit_id) {
                    if ($unit_id == $lu->unit_id) {
                        $learners[$lu->learner_id] = $j+1;
                        break;
                    }
                }
            }
        }

        foreach ($learners as $learner_id => $order) {
            LearnerUnits::where(['course_id' => $this->course_id, 'learner_id' => $learner_id])->delete();
            foreach ($this->unit_ids as $i => $unit_id) {
                if ($i >= $order) {
                    break;
                }
                LearnerUnits::changeF2FStatus($learner_id, $unit_id, true);
            }
        }
    }
}
