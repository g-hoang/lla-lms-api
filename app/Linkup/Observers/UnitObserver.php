<?php

namespace App\Linkup\Observers;

use App\Events\UserLog;
use App\Models\Unit;

/**
 * Class UnitObserver
 *
 * @package App\Linkup\Observers
 */
class UnitObserver
{

    /**
     * Listen to creating
     *
     * @param Unit $unit
     * @return null
     */
    public function creating(Unit $unit)
    {
    }

    /**
     * Listen to the created event.
     *
     * @param Unit $unit
     * @return void
     */
    public function created(Unit $unit)
    {
        event(new Userlog($unit->id, 'UNIT_CREATED', ""));
    }

    public function updated(Unit $unit)
    {
        event(new Userlog($unit->id, 'UNIT_UPDATED', ""));
    }

    /**
     * Listen to the deleting event.
     *
     * @param Unit $unit
     * @return void
     */
    public function deleting(Unit $unit)
    {
        event(new Userlog($unit->id, 'UNIT_DELETED', ""));
    }
}
