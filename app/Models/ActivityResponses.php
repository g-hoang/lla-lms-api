<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityResponses extends Model
{
    /**
     * Table Name
     *
     * @var string
     */
    protected $table = 'activity_responses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'learner_id', 'activity_id', 'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public static function getHistory($learner_id, $activity, $recursive = true)
    {
        $rec = self::where('activity_id', $activity->id)
            ->where('learner_id', $learner_id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$rec) {
            return [];
        }

        $return = json_decode($rec->data);

        if ($recursive) {
            $textoutputs = [];
            foreach ($activity->components as $component) {
                if ($component->component_type == 'TEXT_OUTPUT') {
                    if ($component->data) {
                        $data = $component->data;
                        $data['component'] = $component;
                        if (isset($data['id']) && $data['id']) {
                            $textinput = ActivityComponent::find($data['id']);
                            $textinput->load('activity');
                            $data['activity'] = $textinput->activity;
                            $textoutputs[] = $data;
                        }
                    }
                }
            }

            foreach ($textoutputs as $out) {
                $history = self::getHistory($learner_id, $out['activity'], false);
                foreach ($history as $comp) {
                    if ($comp->id == $out['id']) {
                        $return[] = ["id" => $out['component']['id'], "component_type" => "TEXT_OUTPUT", "answers" => $comp->answers];
                    }
                }
            }
        }

        if (!$return) {
            return [];
        }

        $active_components_ids = array_column($activity->components->toArray(), 'id');

        $active_components = [];

        foreach ($return as $component) {
            if (isset($component->id) && in_array($component->id, $active_components_ids)) {
                $active_components[] = $component;
            }
        }

        return $active_components;
    }

    public static function getTextInputValue($learner_id, $component_id)
    {
        $textInput = ActivityComponent::find($component_id);
        $activity = Activity::find($textInput->activity_id);

        $history = self::getHistory($learner_id, $activity, false);
        foreach ($history as $comp) {
            if ($comp->id == $component_id) {
                return $comp->answers;
            }
        }
    }
}
