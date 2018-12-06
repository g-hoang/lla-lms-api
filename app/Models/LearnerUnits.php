<?php

namespace App\Models;

use App\Events\UserLog;
use Illuminate\Database\Eloquent\Model;

class LearnerUnits extends Model
{
    protected $fillable = ['learner_id', 'unit_id', 'course_id', 'order'];

    public static function changeF2FStatus($learner_id, $unit_id, $status)
    {
        $unit = Unit::find($unit_id);

        if ($status) {
            self::firstOrCreate(['learner_id' => $learner_id, 'unit_id' => $unit_id], ['course_id' => $unit->course_id, 'order' => $unit->order]);
            event(new Userlog($learner_id, 'F2F_COMPLETED', json_encode(['unit_id' => $unit_id, 'course_id' => $unit->course_id, 'order' => $unit->order])));
        } else {
            self::where(['learner_id' => $learner_id, 'unit_id' => $unit_id])->delete();
            event(new Userlog($learner_id, 'F2F_UNCOMPLETED', json_encode(['unit_id' => $unit_id])));
        }
    }
}
