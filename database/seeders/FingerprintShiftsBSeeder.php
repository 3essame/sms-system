<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FingerprintShiftsBSeeder extends Seeder
{
    public function run()
    {
        // تعريف النوبات
        $shifts = [
            'm' => [ // نوبة صباحية
                'time_in' => '07:00:00',
                'time_out' => '19:00:00',
                'next_day' => false
            ],
            'n' => [ // نوبة ليلية
                'time_in' => '19:00:00',
                'time_out' => '07:00:00',
                'next_day' => true
            ]
        ];

        // نمط النوبات: يوم صباحي، يوم ليلي، يومين راحة
        $shiftPattern = ['m', 'n', 'off', 'off'];
        
        // بداية من بعد يومين من آخر تاريخ في الجدول
        $lastRecord = DB::table('fingerprintshifts')->orderBy('datetimein', 'desc')->first();
        $startDate = $lastRecord ? Carbon::parse($lastRecord->datetimein)->addDays(2) : Carbon::now()->startOfDay();
        
        // إضافة 40 يوم لضمان تغطية 20 يوم عمل (كل 4 أيام = 2 أيام عمل)
        for ($day = 0; $day < 40; $day++) {
            $currentDate = $startDate->copy()->addDays($day);
            
            // تحديد نوع النوبة لهذا اليوم
            $patternIndex = $day % count($shiftPattern);
            $currentShift = $shiftPattern[$patternIndex];
            
            // تخطي أيام الراحة
            if ($currentShift === 'off') {
                continue;
            }
            
            // تعيين وقت بداية ونهاية النوبة
            $dateTimeIn = $currentDate->copy();
            $dateOut = $currentDate->copy();
            
            // تعيين وقت بداية النوبة
            $timeIn = Carbon::parse($shifts[$currentShift]['time_in']);
            $dateTimeIn->setHour($timeIn->hour)->setMinute($timeIn->minute)->setSecond(0);
            
            // تعيين وقت نهاية النوبة
            $timeOut = Carbon::parse($shifts[$currentShift]['time_out']);
            if ($shifts[$currentShift]['next_day']) {
                $dateOut->addDay();
            }
            $dateOut->setHour($timeOut->hour)->setMinute($timeOut->minute)->setSecond(0);

            DB::table('fingerprintshifts')->insert([
                'shifttype' => $currentShift, // m, n, off
                'datetimein' => $dateTimeIn,
                'timein' => $shifts[$currentShift]['time_in'],
                'dateout' => $dateOut->toDateString(),
                'timeout' => $shifts[$currentShift]['time_out'],
                'shift' => 'B' // نوبة B
            ]);
        }
    }
}
