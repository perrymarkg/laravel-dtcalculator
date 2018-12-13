<?php

namespace Prymag\DtCalculator;

use \Carbon\Carbon;

class DtCalculatorService {

    protected $carbon;

    protected $int;

    protected $interval;

    protected $the_date;

    public function __construct(Carbon $carbon)
    {
        $this->carbon = $carbon;
    }

    public function setDate(Carbon $date)
    {
        $this->the_date = $date;

        return $this;
    }

    public function getTheDate()
    {
        return $this->the_date;
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

    public function compute()
    {
        return $this->int * 3;
    }

}