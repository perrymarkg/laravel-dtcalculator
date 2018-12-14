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

    public function testCanAddDay()
    {
        $date = \Carbon\Carbon::create(2018, 8, 25, 3, 23);

        $result = $this->dt_calculator->setDate($date)->add(3)->days()->compute();
        
        $str = $result->format('Y-m-d h:i A');
        
        $this->assertEquals($str, '2018-08-28 03:23 AM');
    }

    public function testCanSkipDays()
    {
        $date = \Carbon\Carbon::create(2018, 8, 25, 3, 23);

        $dates = collect([
            \Carbon\Carbon::create(2018, 8, 28),
            \Carbon\Carbon::create(2018, 8, 26),
            \Carbon\Carbon::create(2018, 8, 25),
            \Carbon\Carbon::create(2018, 8, 27),
            \Carbon\Carbon::create(2018, 8, 31),
            \Carbon\Carbon::create(2018, 9, 2),
            \Carbon\Carbon::create(2018, 9, 3),
        ]);

        $result = $this->dt_calculator
            ->setDate($date)
            ->add(3)
            ->days()
            ->skipDates($dates)
            ->compute();

        $str = $result->format('Y-m-d h:i A');
        
        $this->assertEquals($str, '2018-09-01 03:23 AM');
    }

    public function testCanSkipWorkingDays()
    {
        $date = \Carbon\Carbon::create(2018, 12, 14, 15, 23);

        $working_days = collect([
            '0' => ['enabled' => false, 'start' => '08:30', 'end' => '14:30'], // Sunday
            '1' => ['enabled' => true, 'start' => '08:30', 'end' => '14:30'], // Monday
            '2' => ['enabled' => true, 'start' => '12:30', 'end' => '14:30'], // Tuesday
            '3' => ['enabled' => false, 'start' => '08:30', 'end' => '14:30'], // Wednesday
            '4' => ['enabled' => false, 'start' => '08:30', 'end' => '14:30'], // Thursday
            '5' => ['enabled' => true, 'start' => '09:30', 'end' => '14:30'], // Friday
            '6' => ['enabled' => false, 'start' => '08:30', 'end' => '14:30'], // Saturday
        ]);

        $result = $this->dt_calculator
            ->setDate($date)
            ->setWorkingDays($working_days)
            ->add(7)
            ->days()
            ->compute();

        $str = $result->format('Y-m-d h:i A');
        $this->assertEquals('2019-01-01 12:30 PM', $str);
    }

    public function testcanSkipWorkingDaysAndSkipDates()
    {
        $date = \Carbon\Carbon::create(2018, 12, 25, 5, 26);

        $dates = collect([
            \Carbon\Carbon::create(2018, 12, 28),
            \Carbon\Carbon::create(2018, 12, 26),
            \Carbon\Carbon::create(2018, 12, 25),
            \Carbon\Carbon::create(2018, 12, 27),
            \Carbon\Carbon::create(2018, 12, 31),
            \Carbon\Carbon::create(2018, 12, 2),
            \Carbon\Carbon::create(2018, 12, 3),
        ]);

        $working_days = collect([
            '0' => ['enabled' => false, 'start' => '08:30', 'end' => '14:30'], // Sunday
            '1' => ['enabled' => false, 'start' => '08:30', 'end' => '14:30'], // Monday
            '2' => ['enabled' => true, 'start' => '08:30', 'end' => '14:30'], // Tuesday
            '3' => ['enabled' => true, 'start' => '08:30', 'end' => '14:30'], // Wednesday
            '4' => ['enabled' => true, 'start' => '08:30', 'end' => '14:30'], // Thursday
            '5' => ['enabled' => true, 'start' => '08:30', 'end' => '14:30'], // Friday
            '6' => ['enabled' => true, 'start' => '08:30', 'end' => '14:30'], // Saturday
        ]);

        $result = $this->dt_calculator
            ->setDate($date)
            ->setWorkingDays($working_days)
            ->add(3)
            ->days()
            ->skipDates($dates)
            ->compute();

        $str = $result->format('Y-m-d h:i A');
        
        $this->assertEquals($str, '2018-09-01 03:23 AM');
    }
}