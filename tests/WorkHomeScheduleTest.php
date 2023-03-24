<?php

namespace Lee\Tests;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use InvalidArgumentException;
use Lee\InvalidCsvRecordException;
use Lee\InvalidStartDateStatusException;
use Lee\CannotFindDateOnCalendarException;
use Lee\WorkHomeSchedule;

class WorkHomeScheduleTest extends TestCase
{
    protected $workingHomeSchedule;

    protected function setUp(): void
    {
        $filePath = __DIR__ . '/fixtures/2020_calendar.csv';

        $workingHomeSchedule = new WorkHomeSchedule();
        $workingHomeSchedule->startDateStatus = 'office';
        $workingHomeSchedule->csvPath = $filePath;
        $workingHomeSchedule->csvHead = true;

        $this->workingHomeSchedule = $workingHomeSchedule->loadCalendarData();
    }

    public function testNextWorkingDate(): void
    {
        Carbon::mixin($this->workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $nextWorkingDate = $currentDate->nextWorkingDate();

        $carbonDate = $nextWorkingDate['date'];
        $workingStatus = $nextWorkingDate['status'];

        $this->assertInstanceOf(Carbon::class, $carbonDate);
        $this->assertSame('2020-04-07 00:00:00', (string)$carbonDate);
        $this->assertSame('home', $workingStatus);
    }

    public function testNextWorkingDateOnSkipHoliday(): void
    {
        $this->workingHomeSchedule->startDateStatus = 'home';

        Carbon::mixin($this->workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-01');

        $nextWorkingDate = $currentDate->nextWorkingDate();

        $carbonDate = $nextWorkingDate['date'];
        $workingStatus = $nextWorkingDate['status'];

        $this->assertInstanceOf(Carbon::class, $carbonDate);
        $this->assertSame('2020-04-06 00:00:00', (string)$carbonDate);
        $this->assertSame('office', $workingStatus);
    }

    public function testNextWorkingDateOnStartDateStatus(): void
    {
        $this->workingHomeSchedule->startDateStatus = 'home';

        Carbon::mixin($this->workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-07');

        $nextWorkingDate = $currentDate->nextWorkingDate();

        $carbonDate = $nextWorkingDate['date'];
        $workingStatus = $nextWorkingDate['status'];

        $this->assertInstanceOf(Carbon::class, $carbonDate);
        $this->assertSame('2020-04-08 00:00:00', (string)$carbonDate);
        $this->assertSame('office', $workingStatus);
    }

    public function testNextWorkingDates(): void
    {
        $workingHomeSchedule = $this->workingHomeSchedule;
        $workingHomeSchedule->workingDays = 2;

        Carbon::mixin($workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $nextWorkingDates = $currentDate->nextWorkingDates();

        $this->assertIsArray($nextWorkingDates);
        $this->assertCount(2, $nextWorkingDates);
        $this->assertSame('home', $nextWorkingDates[0]['status']);
        $this->assertSame('2020-04-07 00:00:00', (string)$nextWorkingDates[0]['date']);
        $this->assertSame('office', $nextWorkingDates[1]['status']);
        $this->assertSame('2020-04-08 00:00:00', (string)$nextWorkingDates[1]['date']);
    }

    public function testNextWorkingDateOnInvalidStartDateStatus()
    {
        $this->expectException(InvalidStartDateStatusException::class);

        $this->workingHomeSchedule->startDateStatus = 'not_home_or_office';
        Carbon::mixin($this->workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $currentDate->nextWorkingDate();
    }

    public function testPreviousWorkingDate(): void
    {
        Carbon::mixin($this->workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $previousWorkingDate = $currentDate->previousWorkingDate();

        $carbonDate = $previousWorkingDate['date'];
        $workingStatus = $previousWorkingDate['status'];

        $this->assertInstanceOf(Carbon::class, $carbonDate);
        $this->assertSame('2020-04-01 00:00:00', (string)$carbonDate);
        $this->assertSame('home', $workingStatus);
    }

    public function testPreviousWorkingDateOnStartDateStatus(): void
    {
        $this->workingHomeSchedule->startDateStatus = 'home';

        Carbon::mixin($this->workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-07');

        $nextWorkingDate = $currentDate->previousWorkingDate();

        $carbonDate = $nextWorkingDate['date'];
        $workingStatus = $nextWorkingDate['status'];

        $this->assertInstanceOf(Carbon::class, $carbonDate);
        $this->assertSame('2020-04-06 00:00:00', (string)$carbonDate);
        $this->assertSame('office', $workingStatus);
    }

    public function testPreviousWorkingDates(): void
    {
        $workingHomeSchedule = $this->workingHomeSchedule;
        $workingHomeSchedule->workingDays = 2;

        Carbon::mixin($workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $previousWorkingDates = $currentDate->previousWorkingDates();

        $this->assertIsArray($previousWorkingDates);
        $this->assertCount(2, $previousWorkingDates);
        $this->assertSame('home', $previousWorkingDates[0]['status']);
        $this->assertSame('2020-04-01 00:00:00', (string)$previousWorkingDates[0]['date']);
        $this->assertSame('office', $previousWorkingDates[1]['status']);
        $this->assertSame('2020-03-31 00:00:00', (string)$previousWorkingDates[1]['date']);
    }

    public function testPreviousWorkingDateOnInvalidStartDateStatus()
    {
        $this->expectException(InvalidStartDateStatusException::class);

        $this->workingHomeSchedule->startDateStatus = 'not_home_or_office';

        $this->workingHomeSchedule->previousWorkingDate();
    }

    public function testNextWorkingDatesOnInvalidWorkingDays()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->workingHomeSchedule->startDateStatus = 'office';
        $this->workingHomeSchedule->workingDays = -1;

        $this->workingHomeSchedule->nextWorkingDates();
    }

    public function testPreviousWorkingDatesOnInvalidWorkingDays()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->workingHomeSchedule->startDateStatus = 'office';
        $this->workingHomeSchedule->workingDays = -1;

        $this->workingHomeSchedule->previousWorkingDates();
    }

    public function testNextWorkingDatesOnSpecificDateRanges(): void
    {
        $workingHomeSchedule = $this->workingHomeSchedule;
        $workingHomeSchedule->workingDays = 23;

        Carbon::mixin($workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $nextWorkingDates = $currentDate->nextWorkingDates();

        $this->assertIsArray($nextWorkingDates);
        $this->assertCount(23, $nextWorkingDates);
        $this->assertSame('home', $nextWorkingDates[22]['status']);
        $this->assertSame('2020-05-08 00:00:00', (string)$nextWorkingDates[22]['date']);
        $this->assertSame('office', $nextWorkingDates[21]['status']);
        $this->assertSame('2020-05-07 00:00:00', (string)$nextWorkingDates[21]['date']);
    }

    public function testLoadCalendarDataOnInvalidCsvFilePath()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->workingHomeSchedule->csvPath = '/file/path/not/found';
        $this->workingHomeSchedule->loadCalendarData();
    }

    public function testLoadCalendarDataOnInvalidCsvRecord()
    {
        $this->expectException(InvalidCsvRecordException::class);

        $this->workingHomeSchedule->csvPath = __DIR__ . '/fixtures/invalid_calendar.csv';

        $this->workingHomeSchedule->loadCalendarData();
    }

    public function testLoadCalendarDataOnCsvRecordWithInvalidHolidayFormat()
    {
        $this->expectException(InvalidCsvRecordException::class);

        $this->workingHomeSchedule->csvPath = __DIR__ . '/fixtures/invalid_holiday_calendar.csv';

        $this->workingHomeSchedule->loadCalendarData();
    }

    public function testNextWorkingDateOnCannotFindCalendarData()
    {
        $this->expectException(CannotFindDateOnCalendarException::class);

        $workingHomeSchedule = new WorkHomeSchedule();
        $workingHomeSchedule->startDateStatus = 'office';
        $workingHomeSchedule->csvPath = __DIR__ . '/fixtures/not_enough_calendar.csv';
        $workingHomeSchedule = $workingHomeSchedule->loadCalendarData();

        Carbon::mixin($workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $currentDate->nextWorkingDate();
    }

    public function testNextWorkingDatesOnCannotFindCalendarData()
    {
        $this->expectException(CannotFindDateOnCalendarException::class);

        $workingHomeSchedule = new WorkHomeSchedule();
        $workingHomeSchedule->startDateStatus = 'office';
        $workingHomeSchedule->csvPath = __DIR__ . '/fixtures/not_enough_calendar.csv';
        $workingHomeSchedule->workingDays = 2;
        $workingHomeSchedule = $workingHomeSchedule->loadCalendarData();

        Carbon::mixin($workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $currentDate->nextWorkingDates();
    }

    public function testPreviousWorkingDateOnCannotFindCalendarData()
    {
        $this->expectException(CannotFindDateOnCalendarException::class);

        $workingHomeSchedule = new WorkHomeSchedule();
        $workingHomeSchedule->startDateStatus = 'office';
        $workingHomeSchedule->csvPath = __DIR__ . '/fixtures/not_enough_calendar.csv';
        $workingHomeSchedule = $workingHomeSchedule->loadCalendarData();

        Carbon::mixin($workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $currentDate->previousWorkingDate();
    }

    public function testPreviousWorkingDatesOnCannotFindCalendarData()
    {
        $this->expectException(CannotFindDateOnCalendarException::class);

        $workingHomeSchedule = new WorkHomeSchedule();
        $workingHomeSchedule->startDateStatus = 'office';
        $workingHomeSchedule->csvPath = __DIR__ . '/fixtures/not_enough_calendar.csv';
        $workingHomeSchedule->workingDays = 2;
        $workingHomeSchedule = $workingHomeSchedule->loadCalendarData();

        Carbon::mixin($workingHomeSchedule);
        $currentDate = Carbon::create('2020-04-06');

        $currentDate->previousWorkingDates();
    }
}
