<?php

namespace App\Http\Resources;

use App\Models\LearnerUnits;
use App\Models\Progress;
use App\Models\ProgressHistory;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;

class CourseProgress extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request HTTP Request
     *
     * @return array
     */
    public function toArray($request)
    {
        $learner = $this->additional['learner'];
        $totalTimeSpent = 0;

        $return =  [
            'id' => $this->id,
            'name' => $this->name,
            'total_time' => $totalTimeSpent,
            'learner' => Learner::make($learner),
            'units' => ($this->units),
        ];

        foreach ($return['units'] as $i => $unit) {
            $status = 'PENDING';

            $isCompleted = ProgressHistory::where('learner_id', $learner->id)
                ->where('completed_type', 'App\Models\Unit')
                ->where('completed_id', $unit->id)
                ->exists();

            if ($isCompleted) {
                $status = 'COMPLETED';
            } else {
                $isInProgress = Progress::where('unit_id', $unit->id)
                    ->where('learner_id', $learner->id)
                    ->exists();

                if ($isInProgress) {
                    $status = 'IN-PROGRESS';
                }
            }

            $isAttended = LearnerUnits::where('learner_id', $learner->id)
                ->where('unit_id', $unit->id)
                ->exists();

            $score = null;
            $last_access = null;
            $summary = Progress::getLearnerUnitScore($learner->id, $unit->id);
            if ($summary) {
                if ($summary['total']) {
                    $score = ($summary['correct'] / $summary['total']) * 100;
                    $score = round($score, 0) . '%';
                }
                if ($summary['total']) {
                    $last_access = $summary['created_at'];
                }
            }

            $last_access = Progress::getLearnerUnitLastAccess($learner->id, $unit->id);


            $timeSpent = Progress::getLearnerUnitTimeSpent($learner->id, $unit->id);
            $totalTimeSpent += $timeSpent;

            $return['units'][$i]['status'] = $status;
            $return['units'][$i]['attended_f2f'] = $isAttended;
            $return['units'][$i]['score'] = $score;
            $return['units'][$i]['last_access'] = $last_access;
            $return['units'][$i]['total_time'] = $timeSpent ? gmdate('H:i:s', $timeSpent) : null;
        }

        $return['total_time'] = gmdate('H:i:s', $totalTimeSpent);

        return $return;
    }

}
