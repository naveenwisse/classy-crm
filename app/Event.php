<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $dates = ['start_date_time', 'end_date_time'];

    public function attendee(){
        return $this->hasOne(EventAttendee::class, 'event_id');
    }

    public function event_status() {
        return $this->hasMany(EventStatus::class);
    }

    public function getUsers(){
        $userArray = [];
        foreach ($this->attendee as $attendee) {
           array_push($userArray, $attendee->user()->select('id', 'email', 'name')->first());
        }
        return collect($userArray);
    }
}
