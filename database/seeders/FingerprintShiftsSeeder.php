<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FingerprintShiftsSeeder extends Seeder
{
    public function run()
    {
        // مسح البيانات القديمة
        DB::table('fingerprintshifts')->truncate();

        // تعريف النوبات
        $shifts = [
            'day' => [ // نوبة صباحية
                'time_in' => '07:00:00',
                'time_out' => '19:00:00'
            ],
            'night' => [ // نوبة ليلية
                'time_in' => '19:00:00',
                'time_out' => '07:00:00'
            ]
        ];

        // بداية من 1-12-2023
        $startDate = Carbon::create(2023, 12, 1)->startOfDay();
        $workDays = 0;
        $currentDate = $startDate->copy();
        
        // إضافة نوبة A
        while ($workDays < 20) {
            // يوم 1: نوبة صباحية
            $dateTimeIn = $currentDate->copy();
            $dateOut = $currentDate->copy();
            
            $timeIn = Carbon::parse($shifts['day']['time_in']);
            $dateTimeIn->setHour($timeIn->hour)->setMinute($timeIn->minute)->setSecond(0);
            
            $timeOut = Carbon::parse($shifts['day']['time_out']);
            $dateOut->setHour($timeOut->hour)->setMinute($timeOut->minute)->setSecond(0);

            DB::table('fingerprintshifts')->insert([
                'shifttype' => 'm',
                'datetimein' => $dateTimeIn,
                'timein' => $shifts['day']['time_in'],
                'dateout' => $dateOut->toDateString(),
                'timeout' => $shifts['day']['time_out'],
                'shift' => 'A'
            ]);

            $workDays++;
            $currentDate->addDay();

            // يوم 2: نوبة ليلية
            if ($workDays < 20) {
                $dateTimeIn = $currentDate->copy();
                $dateOut = $currentDate->copy()->addDay();
                
                $timeIn = Carbon::parse($shifts['night']['time_in']);
                $dateTimeIn->setHour($timeIn->hour)->setMinute($timeIn->minute)->setSecond(0);
                
                $timeOut = Carbon::parse($shifts['night']['time_out']);
                $dateOut->setHour($timeOut->hour)->setMinute($timeOut->minute)->setSecond(0);

                DB::table('fingerprintshifts')->insert([
                    'shifttype' => 'n',
                    'datetimein' => $dateTimeIn,
                    'timein' => $shifts['night']['time_in'],
                    'dateout' => $dateOut->toDateString(),
                    'timeout' => $shifts['night']['time_out'],
                    'shift' => 'A'
                ]);

                $workDays++;
                $currentDate->addDays(3); // يومين راحة + يوم العمل التالي
            }
        }

        // إعادة تعيين المتغيرات للنوبة B
        $workDays = 0;
        $currentDate = $startDate->copy();
        
        // إضافة نوبة B
        while ($workDays < 20) {
            // يوم 1: نوبة ليلية
            $dateTimeIn = $currentDate->copy();
            $dateOut = $currentDate->copy()->addDay();
            
            $timeIn = Carbon::parse($shifts['night']['time_in']);
            $dateTimeIn->setHour($timeIn->hour)->setMinute($timeIn->minute)->setSecond(0);
            
            $timeOut = Carbon::parse($shifts['night']['time_out']);
            $dateOut->setHour($timeOut->hour)->setMinute($timeOut->minute)->setSecond(0);

            DB::table('fingerprintshifts')->insert([
                'shifttype' => 'n',
                'datetimein' => $dateTimeIn,
                'timein' => $shifts['night']['time_in'],
                'dateout' => $dateOut->toDateString(),
                'timeout' => $shifts['night']['time_out'],
                'shift' => 'B'
            ]);

            $workDays++;
            $currentDate->addDays(3); // يومين راحة + يوم العمل التالي

            // يوم 4: نوبة صباحية
            if ($workDays < 20) {
                $dateTimeIn = $currentDate->copy();
                $dateOut = $currentDate->copy();
                
                $timeIn = Carbon::parse($shifts['day']['time_in']);
                $dateTimeIn->setHour($timeIn->hour)->setMinute($timeIn->minute)->setSecond(0);
                
                $timeOut = Carbon::parse($shifts['day']['time_out']);
                $dateOut->setHour($timeOut->hour)->setMinute($timeOut->minute)->setSecond(0);

                DB::table('fingerprintshifts')->insert([
                    'shifttype' => 'm',
                    'datetimein' => $dateTimeIn,
                    'timein' => $shifts['day']['time_in'],
                    'dateout' => $dateOut->toDateString(),
                    'timeout' => $shifts['day']['time_out'],
                    'shift' => 'B'
                ]);

                $workDays++;
                $currentDate->addDay();
            }
        }
    }
}
