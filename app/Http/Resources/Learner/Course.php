<?php

namespace App\Http\Resources\Learner;

use App\Models\Progress;
use App\Models\ProgressHistory;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;

class Course extends Resource
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

        $return =  [
            'id' => $this->id,
            'name' => $this->name,
            'units' => ($this->units)
        ];

        if ($this->units) {
            $progress = Auth::user()->getProgress();
            $return['units'] = $this->units;


            foreach ($return['units'] as $x => $unit) {
                // Special user always has access and considered as completed
                if (Auth::user()->id == 1) {
                    $return['units'][$x]['progress'] = 'COMPLETED';
                    continue;
                }

                $return['units'][$x]['progress'] = 'PENDING';

                $isCompleted = ProgressHistory::where('learner_id', Auth::user()->id)
                    ->where('completed_type', 'App\Models\Unit')
                    ->where('completed_id', $unit->id)
                    ->exists();

                if ($isCompleted) {
                    $return['units'][$x]['progress'] = 'COMPLETED';
                } else {
                    $isInProgress = Progress::where('unit_id', $unit->id)
                        ->where('learner_id', Auth::user()->id)
                        ->exists();

                    if ($isInProgress) {
                        $return['units'][$x]['progress'] = 'IN-PROGRESS';
                    }
                }
            }


            $return['progress_next'] = $progress['next'];
        }

        return $return;
    }

}
