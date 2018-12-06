<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressHistory extends Model
{

    protected $fillable = [
        'learner_id', 'completed_id', 'completed_type', 'meta_data', 'attempt'
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    /**
     * Learner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    /**
     * Get all of the owning passwordResettable models.
     */
    public function completable()
    {
        return $this->morphTo();
    }

    /**
     * Update progress history
     *
     * @param Activity $activity
     * @param Learner $learner
     * @param array $data
     */
    public function updateHistory(Activity $activity, Learner $learner, $data = [])
    {
        $this->updateActivity($activity->id, $learner->id, $data);

        if ($activity->isTheLastActivityOfALesson()) {
            $this->updateLesson($activity->lesson_id, $learner->id);

            if ($activity->lesson->isTheLastLessonOfAnUnit()) {
                $this->updateUnit($activity->lesson->unit_id, $learner->id);

                if ($activity->lesson->unit->isTheLastUnitOfACourse()) {
                    $this->updateUnit($activity->lesson->unit->course_id, $learner->id);
                }
            }
        }
    }

    /**
     * Activity
     *
     * @param $activity_id
     * @param $learner_id
     * @param $data
     * @return mixed
     */
    public function updateActivity($activity_id, $learner_id, $data)
    {
        $progress = ProgressHistory::where('learner_id', $learner_id)
            ->where('completed_type', Activity::class)
            ->where('completed_id', $activity_id)
            ->first();

        if ($progress) {
            return $progress->fill([
                'meta_data' => $data,
                'attempt' => ($progress->attempt + 1)
            ])->save();

        }

        return ProgressHistory::create([
            'learner_id' => $learner_id,
            'completed_id' => $activity_id,
            'completed_type' => Activity::class,
            'meta_data' => $data,
            'attempt' => 1
        ]);
    }

    /**
     * Lesson
     *
     * @param $lesson_id
     * @param $learner_id
     * @return mixed
     */
    public function updateLesson($lesson_id, $learner_id)
    {
        $progress = ProgressHistory::where('learner_id', $learner_id)
            ->where('completed_type', Lesson::class)
            ->where('completed_id', $lesson_id)
            ->first();

        if ($progress) {
            return $progress->fill([
                'meta_data' => [],
                'attempt' => ($progress->attempt + 1),
            ])->save();

        }

        return ProgressHistory::create([
            'learner_id' => $learner_id,
            'completed_id' => $lesson_id,
            'completed_type' => Lesson::class,
            'meta_data' => [],
            'attempt' => 1
        ]);
    }

    /**
     * Unit
     *
     * @param $unit_id
     * @param $learner_id
     * @return mixed
     */
    public function updateUnit($unit_id, $learner_id)
    {
        $progress = ProgressHistory::where('learner_id', $learner_id)
            ->where('completed_type', Unit::class)
            ->where('completed_id', $unit_id)
            ->first();

        if ($progress) {
            return $progress->fill([
                'meta_data' => [],
                'attempt' => ($progress->attempt + 1),
                'is_completed' => true,
            ])->save();

        }

        return ProgressHistory::create([
            'learner_id' => $learner_id,
            'completed_id' => $unit_id,
            'completed_type' => Unit::class,
            'meta_data' => [],
            'attempt' => 1,
            'is_completed' => true,
        ]);
    }

    /**
     * Update Course
     *
     * @param $course_id
     * @param $learner_id
     * @return mixed
     */
    public function updateCourse($course_id, $learner_id)
    {
        $progress = ProgressHistory::where('learner_id', $learner_id)
            ->where('completed_type', Course::class)
            ->where('completed_id', $course_id)
            ->first();

        if ($progress) {
            return $progress->fill([
                'meta_data' => [],
                'attempt' => ($progress->attempt + 1),
            ])->save();

        }

        return ProgressHistory::create([
            'learner_id' => $learner_id,
            'completed_id' => $course_id,
            'completed_type' => Course::class,
            'meta_data' => [],
            'attempt' => 1
        ]);
    }
}
