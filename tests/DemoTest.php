<?php
 use \PHPUnit\Framework\TestCase;

use Prymag\DtCalculator\DtCalculatorService;

class DemoTest extends TestCase
{

    protected $dt_calculator;

    public function setUp()
    {
        parent::setUp();
        $this->dt_calculator = app()->make(DtCalculatorService::class);
    }

    public function testCanSetInterval()
    {
        $interval = $this->dt_calculator->minutes()->getInterval();
        $this->assertEquals($interval, 'minutes');
        $interval = $this->dt_calculator->hours()->getInterval();
        $this->assertEquals($interval, 'hours');
        $interval = $this->dt_calculator->days()->getInterval();
        $this->assertEquals($interval, 'days');
    }

    public function testCanSetTheDate()
    {
        dd($this->dt_calculator->setDate(\Carbon\Carbon::now()));
    }
}