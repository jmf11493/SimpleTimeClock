<?php

class User
{
    private $id;

    private $name;

    private $admin;

    private $activeShift;

    private $activeBreak;

    private $activeLunch;

    private $shiftData = [];

    private $shiftStarted = 'Shift started';
    private $shiftEnded   = 'Shift ended';
    private $breakStarted = 'Break started';
    private $breakEnded   = 'Break ended';
    private $lunchStarted = 'Lunch started';
    private $lunchEnded   = 'Lunch ended';

    public function __construct($employeeId, $name, $admin = false) {
        $this->id = $employeeId;
        $this->name = $name;
        $this->admin = $admin;
        $this->activeShift = false;
        $this->activeBreak = false;
        $this->activeLunch = false;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function isAdmin() {
        return $this->admin;
    }

    public function getAdminStatus() {
        return $this->isAdmin() ? "Yes" : "No";
    }

    public function getShiftData() {
        return $this->shiftData;
    }

    public function startShift() {
        $this->activeShift = true;
        $this->recordUserActivity($this->shiftStarted);
    }

    public function endShift() {
        $this->activeShift = false;
        $this->recordUserActivity($this->shiftEnded);
    }

    public function hasActiveShift() {
        return $this->activeShift;
    }

    public function noActiveShift() {
        return !$this->hasActiveShift();
    }

    public function startBreak() {
        $this->activeBreak = true;
        $this->recordUserActivity($this->breakStarted);
    }

    public function endBreak() {
        $this->activeBreak = false;
        $this->recordUserActivity($this->breakEnded);
    }

    public function hasActiveBreak() {
        return $this->activeBreak;
    }

    public function noActiveBreak() {
        return !$this->hasActiveBreak();
    }

    public function startLunch() {
        $this->activeLunch = true;
        $this->recordUserActivity($this->lunchStarted);
    }

    public function endLunch() {
        $this->activeLunch = false;
        $this->recordUserActivity($this->lunchEnded);
    }

    public function hasActiveLunch() {
        return $this->activeLunch;
    }

    public function noActiveLunch() {
        return !$this->hasActiveLunch();
    }

    public function hasNoBreaks() {
        return $this->noActiveBreak() && $this->noActiveLunch();
    }

    private function recordUserActivity($action) {
        $date = date('m-d-Y h:i:sa');
        $this->shiftData[] = $date . ' ' .  $action;
    }
}