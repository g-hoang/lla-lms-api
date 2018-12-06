<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearnerLog extends Model
{
    /**
     * Table Name
     *
     * @var string
     */
    protected $table = 'learnerlogs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'learner_id', 'foreign_key', 'event', 'details'
    ];
}
