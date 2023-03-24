<?php

namespace Lee;

use Closure;
use InvalidArgumentException;
use Carbon\Carbon;

class WorkHomeSchedule
{
    public $dateFormat = 'Y/m/d';

    public $startDateStatus = '';

    public $csvPath = '';

    public $workingDays = 1;

    public $csvHead = true;

    public $calendars = [];

    private function findNextWorkingDate(): Closure
    {
        $calendars = $this->calendars;
        $dateFormat = $this->dateFormat;
        $startDateStatus = $this->startDateStatus;

        return function () use ($calendars, $dateFormat, $startDateStatus) {
            $this->addDay();
            $currentDateString = $this->format($dateFormat);
            $currentDateStatus = '';

            while ($currentDateStatus === '') {
                $calendarCount = 0;
                foreach ($calendars as $calendar) {
                    $calendarCarbon = $calendar['date'];
                    $calendarDateString = $calendarCarbon->format($dateFormat);
                    $isHoliday = $calendar['is_holiday'];

                    if ($currentDateString === $calendarDateString) {
                        if ($isHoliday === '0') {
                            if ($startDateStatus === 'home') {
                                $currentDateStatus = 'office';
                            } else {
                                $currentDateStatus = 'home';
                            }
                            break;
                        }
                    } else {
                        $calendarCount += 1;
                    }
                }

                if ($calendarCount === count($calendars)) {
                    throw new CannotFindDateOnCalendarException('Specific date cannot find on loaded calendar data');
                }

                if ($currentDateStatus === '') {
                    $this->addDay();
                    $currentDateString = $this->format($dateFormat);
                }
            }

            return [
                'date' => $this,
                'status' => $currentDateStatus,
            ];
        };
    }

    private function findNextWorkingDates(): Closure
    {
        $day = $this->workingDays;
        $calendars = $this->calendars;
        $dateFormat = $this->dateFormat;
        $startDateStatus = $this->startDateStatus;

        return function () use ($calendars, $dateFormat, $startDateStatus, $day) {
            $nextWorkingDates = [];
            $this->addDay();
            $currentDateString = $this->format($dateFormat);
            $currentDateStatus = '';
            $dayRange = range(1, $day);

            foreach ($dayRange as $value) {
                while ($currentDateStatus === '') {
                    $calendarCount = 0;
                    foreach ($calendars as $calendar) {
                        $calendarCarbon = $calendar['date'];
                        $calendarDateString = $calendarCarbon->format($dateFormat);
                        $isHoliday = $calendar['is_holiday'];

                        if ($currentDateString === $calendarDateString) {
                            if ($isHoliday === '0') {
                                if ($startDateStatus === 'home') {
                                    $currentDateStatus = 'office';
                                } else {
                                    $currentDateStatus = 'home';
                                }
                                break;
                            }
                        } else {
                            $calendarCount += 1;
                        }
                    }

                    if ($calendarCount === count($calendars)) {
                        throw new CannotFindDateOnCalendarException('Specific date cannot find on loaded calendar data');
                    }

                    if ($currentDateStatus === '') {
                        $this->addDay();
                        $currentDateString = $this->format($dateFormat);
                    }
                }

                $tempDateCarbon = clone $this;
                $nextWorkingDates[] =  [
                    'date' => $tempDateCarbon,
                    'status' => $currentDateStatus,
                ];

                $startDateStatus = $currentDateStatus;
                $currentDateStatus = '';
                $this->addDay();
                $currentDateString = $this->format($dateFormat);
            }

            return $nextWorkingDates;
        };
    }

    private function findPreviousWorkingDates(): Closure
    {
        $day = $this->workingDays;
        $calendars = $this->calendars;
        $dateFormat = $this->dateFormat;
        $startDateStatus = $this->startDateStatus;

        return function () use ($calendars, $dateFormat, $startDateStatus, $day) {
            $nextWorkingDates = [];
            $this->subDay();
            $currentDateString = $this->format($dateFormat);
            $currentDateStatus = '';
            $dayRange = range(1, $day);

            foreach ($dayRange as $value) {
                while ($currentDateStatus === '') {
                    $calendarCount = 0;
                    foreach ($calendars as $calendar) {
                        $calendarCarbon = $calendar['date'];
                        $calendarDateString = $calendarCarbon->format($dateFormat);
                        $isHoliday = $calendar['is_holiday'];

                        if ($currentDateString === $calendarDateString) {
                            if ($isHoliday === '0') {
                                if ($startDateStatus === 'home') {
                                    $currentDateStatus = 'office';
                                } else {
                                    $currentDateStatus = 'home';
                                }
                                break;
                            }
                        } else {
                            $calendarCount += 1;
                        }
                    }

                    if ($calendarCount === count($calendars)) {
                        throw new CannotFindDateOnCalendarException('Specific date cannot find on loaded calendar data');
                    }

                    if ($currentDateStatus === '') {
                        $this->subDay();
                        $currentDateString = $this->format($dateFormat);
                    }
                }

                $tempDateCarbon = clone $this;
                $nextWorkingDates[] =  [
                    'date' => $tempDateCarbon,
                    'status' => $currentDateStatus,
                ];

                $startDateStatus = $currentDateStatus;
                $currentDateStatus = '';
                $this->subDay();
                $currentDateString = $this->format($dateFormat);
            }

            return $nextWorkingDates;
        };
    }

    private function findPreviousWorkingDate(): Closure
    {
        $calendars = $this->calendars;
        $dateFormat = $this->dateFormat;
        $startDateStatus = $this->startDateStatus;

        return function () use ($calendars, $dateFormat, $startDateStatus) {
            $this->subDay();
            $currentDateString = $this->format($dateFormat);
            $currentDateStatus = '';

            while ($currentDateStatus === '') {
                $calendarCount = 0;
                foreach ($calendars as $calendar) {
                    $calendarCarbon = $calendar['date'];
                    $calendarDateString = $calendarCarbon->format($dateFormat);
                    $isHoliday = $calendar['is_holiday'];

                    if ($currentDateString === $calendarDateString) {
                        if ($isHoliday === '0') {
                            if ($startDateStatus === 'home') {
                                $currentDateStatus = 'office';
                            } else {
                                $currentDateStatus = 'home';
                            }
                            break;
                        }
                    } else {
                        $calendarCount += 1;
                    }
                }

                if ($calendarCount === count($calendars)) {
                    throw new CannotFindDateOnCalendarException('Specific date cannot find on loaded calendar data');
                }

                if ($currentDateStatus === '') {
                    $this->subDay();
                    $currentDateString = $this->format($dateFormat);
                }
            }

            return [
                'date' => $this,
                'status' => $currentDateStatus,
            ];
        };
    }

    private function validateStartDateStatus(string $status): bool
    {
        if ($status !== 'home' && $status !== 'office') {
            throw new InvalidStartDateStatusException('Working status should be home or office');
        }

        return true;
    }

    public function nextWorkingDate(): Closure
    {
        $this->validateStartDateStatus($this->startDateStatus);

        return $this->findNextWorkingDate();
    }

    public function nextWorkingDates(): Closure
    {
        $this->validateStartDateStatus($this->startDateStatus);

        if ($this->workingDays <= 0) {
            throw new InvalidArgumentException('The working day range should be greater than 0');
        }

        if ($this->workingDays === 1) {
            return $this->findNextWorkingDate();
        }

        return $this->findNextWorkingDates();
    }

    public function previousWorkingDates(): Closure
    {
        $this->validateStartDateStatus($this->startDateStatus);

        if ($this->workingDays <= 0) {
            throw new InvalidArgumentException('The working day range should be greater than 0');
        }

        if ($this->workingDays === 1) {
            return $this->findPreviousWorkingDate();
        }

        return $this->findPreviousWorkingDates();
    }

    public function previousWorkingDate(): Closure
    {
        $this->validateStartDateStatus($this->startDateStatus);

        return $this->findPreviousWorkingDate();
    }

    public function loadCalendarData(): self
    {
        $this->calendars = [];

        if (file_exists($this->csvPath) === false) {
            throw new InvalidArgumentException('calendar CSV file path is not found');
        }

        $handler = fopen($this->csvPath, 'r');

        if ($this->csvHead === true) {
            fgets($handler);
        }

        $message = '';

        while (feof($handler) === false) {
            $record = fgets($handler);
            $str = str_getcsv($record);

            $strLen = count($str);
            if ($strLen === 1) {
                if ($str[0] === null) {
                    continue;
                }
            }

            if (count($str) !== 3) {
                $message = 'CSV record length should be 3';
                throw new InvalidCsvRecordException($message);

                break;
            }

            $dateString = $str[0];

            $isHoliday = (string)$str[1];

            $dateCarbon = Carbon::parse($dateString, null, $this->dateFormat);

            if ($isHoliday !== '0' && $isHoliday !== '1') {
                $message = 'The holiday value should be 0 or 1';

                throw new InvalidCsvRecordException($message);
            }

            $this->calendars[] = [
                'date' => $dateCarbon,
                'is_holiday' => $isHoliday,
            ];
        }

        fclose($handler);

        return $this;
    }
}
