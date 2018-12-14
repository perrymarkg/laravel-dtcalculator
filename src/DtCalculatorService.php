<?php

namespace Prymag\DtCalculator;

use \Carbon\Carbon;
use Illuminate\Support\Collection;

class DtCalculatorService {

    protected $carbon;

    protected $int;

    protected $interval;

    protected $the_date;

    protected $start_date;

    protected $end_date;

    protected $days_to_skip;

    protected $working_days;

    protected $timezone = 'UTC';

    public function __construct(Carbon $carbon)
    {
        $this->carbon = $carbon;
    }

    public function setDate(Carbon $date)
    {
        $this->the_date = $date;
        $this->start_date = $this->the_date->copy();

        if ($this->days_to_skip) {
            $this->skipDates($this->days_to_skip);
        }

        return $this;
    }

    public function setWorkingDays(Collection $working_days)
    {
        $this->working_days = $working_days;

        return $this;
    }

    public function getDate()
    {
        return $this->the_date;
    }

    public function getStartDate()
    {
        if ($this->interval == 'minutes') {
            return $this->start_date
                ->copy()
                ->startOfDay();
        } else {
            // Always start at the next day for the start
            return $this->start_date
                ->copy()
                ->addDay()
                ->startOfDay();
        }
    }

    public function getEndDate()
    {
        return $this->the_date->copy()->endOfDay();
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function add($int)
    {
        $this->int = $int;

        return $this;
    }

    public function days()
    {
        $this->setInterval('days');

        return $this;
    }

    public function hours()
    {
        $this->setInterval('hours');

        return $this;
    }

    public function minutes()
    {
        $this->setInterval('minutes');

        return $this;
    }

    protected function setInterval($type)
    {
        $this->interval = $type;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function skipDates(Collection $dates)
    {
        //
        if ($dates->count()) {
            //
            $this->days_to_skip = $dates->sort();
        }
        
        return $this;
    }

    public function compute()
    {
        $this->addInterval();

        $this->processDates();
        //$this->processSkippableDates();

        return $this->the_date;
    }

    public function processDates()
    {
        $this->adjustToWorkingDay();

        $start = $this->getStartDate();
        $end = $this->getEndDate();
        
        if ($this->interval == 'days') {
            $periods = \Carbon\CarbonPeriod::create($start, '1 day', $end);
            $ctr = 0;
            foreach ($periods as $date) {
                if ($this->isDateSkippable($date)) {
                    $ctr++;
                }
            }
            
            if ($ctr) {
                $this->int = $ctr;
                $this->start_date = $this->the_date->copy();
                $this->compute();
            }
        } else {
            
            if ($this->isDateSkippable($this->the_date->copy())) {
                $this->the_date->addDay();

                if ($this->interval == 'minutes') {
                    $this->the_date->setTime(0,0,0);
                }

                if ($this->interval == 'hours') {
                    $this->the_date->setTime(0, $this->start_date->format('i'), 0);
                }
                
                $this->compute();
            }
        }
    }

    public function isDateSkippable($date)
    {
        if ($this->working_days && !$this->working_days[$date->format('w')]['enabled']) {
            return true;
        }
        
        if (!empty($this->days_to_skip) && $this->days_to_skip->count()) {
            foreach ($this->days_to_skip as $skip_date) {
                //
                $start_day = $skip_date->copy()->startOfDay();
                $end_day = $skip_date->copy()->endOfDay();
                //
                if ($date->format('m-d') === $skip_date->format('m-d')) {
                    return true;
                    break;
                }
            }
        }
    }

    public function adjustToWorkingDay()
    {
        if ($this->working_days && $this->working_days[$this->the_date->format('w')]['enabled']) {
            $working_day = $this->working_days[$this->the_date->format('w')];
            $format = 'Y-m-d H:i';
            $date_start = $this->the_date->format('Y') . '-' . $this->the_date->format('m') . '-' . $this->the_date->format('d') . ' ' . $working_day['start'];
            $date_end = $this->the_date->format('Y') . '-' . $this->the_date->format('m') . '-' . $this->the_date->format('d') . ' ' . $working_day['end'];
            
            $working_day_start = \Carbon\Carbon::createFromFormat($format, $date_start);
            $working_day_end = \Carbon\Carbon::createFromFormat($format, $date_end);

            if ($this->the_date->lessThan($working_day_start)) {
                $this->the_date->setTime($working_day_start->format('H'), $working_day_start->format('i'), $working_day_start->format('s'));
            }

            if ($this->the_date->greaterThan($working_day_end)) {
                // Get the next working day
                $next_working_day = $this->getNextWorkingDay($this->the_date);
                $next_working_date = $this->the_date->format('Y') . '-' . $this->the_date->format('m') . '-' . $this->the_date->format('d') . ' ' . $next_working_day['start'];
                $next_working_date = \Carbon\Carbon::createFromFormat($format, $next_working_date);
                $this->the_date->addDay();
                $this->the_date->setTime($next_working_date->format('H'), $next_working_date->format('i'), $next_working_date->format('s'));
            }
        }
    }

    /* public function processSkippableDates()
    {
        if (!empty($this->days_to_skip) && $this->days_to_skip->count()) {
            //
            $days_to_skip = $this->days_to_skip;
            $start = $this->getStartDate();
            //
            foreach ($days_to_skip as $k => $date) {
                // Remove dates that are less than the start date
                // and move to next loop
                if ($date->lessThan($this->start_date)) {
                    unset($days_to_skip[$k]);
                    continue;
                }
                //
                $end = $this->getEndDate();
                // Always assume that the skippable dates are sorted in ascending order
                // Check if date in skippable is within range
                if ($date->greaterThan($start) && $date->lessThan($end)) {
                    $this->addInterval(1);
                    $this->processNonWorkingDates();
                }

                unset($days_to_skip[$k]);
            }
            return;
        }

        $this->processNonWorkingDates();
    } */

    /* public function processNonWorkingDates()
    {
        if ($this->working_days) {
            $working_days = $this->working_days;
            $start = $this->getStartDate();
            $end = $this->getEndDate();
            $periods = \Carbon\CarbonPeriod::create($start, '1 day', $end);

            // Check individual dates if 
            foreach ($periods as $period) {
                if (!$working_days[$period->format('w')]['enabled']) {
                    $this->addInterval(1);
                }
            }
            while (!$working_days[$end->format('N')]['enabled']) {
                $this->addInterval(1);
                
                $end = $this->getEndDate();
            }
            // Check the time of the working day
            $working_day = $working_days[$end->format('N')];
            $format = 'Y-m-d H:i';
            $date_start = $end->format('Y') . '-' . $end->format('m') . '-' . $end->format('d') . ' ' . $working_day['start'];
            $date_end = $end->format('Y') . '-' . $end->format('m') . '-' . $end->format('d') . ' ' . $working_day['end'];
            
            $working_day_start = \Carbon\Carbon::createFromFormat($format, $date_start);
            $working_day_end = \Carbon\Carbon::createFromFormat($format, $date_end);

            if ($this->the_date->lessThan($working_day_start)) {
                $this->the_date->setTime($working_day_start->format('H'), $working_day_start->format('i'), $working_day_start->format('s'));
            }
            
            if ($this->the_date->greaterThan($working_day_end)) {
                // Get the next working day
                $next_working_day = $this->getNextWorkingDay($this->the_date);
                
                // @todo here.
                $this->the_date->setTime($next_working_day->format('H'), $next_working_day->format('i'), $next_working_day->format('s'));
            }
        }
    } */
    
    public function getNextWorkingDay($date)
    {
        $index = $date->format('N') + 1;
        $index = $index > 6 ? 0 : $index;
        return $this->working_days[$index]['enabled'] ?
            $this->working_days[$index] :
            $this->getNextWorkingDay($date->copy()->addDay());
    }

    public function addInterval($inc = false)
    {
        $interval = 'add' . ucfirst($this->interval);
        $int = $inc ? $inc : $this->int;
        $this->the_date->$interval($int);
    }

    public function addDay()
    {
        $this->the_date->addDay();
    }

    public function isDateToBeSkipped()
    {
        if (empty($this->days_to_skip)) 
            return false;
        
        foreach($this->days_to_skip as $k => $day) {
            if ($this->the_date->format('Y-m-d') == $day->format('Y-m-d')) {
                //unset($this->days_to_skip[$k]);
                return true;
            }
        }

        return false;
    }
}