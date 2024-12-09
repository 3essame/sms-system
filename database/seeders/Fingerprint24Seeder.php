<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Fingerprint24Seeder extends Seeder
{
    public function run()
    {
        // مسح البيانات القديمة
        DB::table('fingerprint24')->truncate();

        // الحصول على بيانات النوبات
        $shifts = DB::table('fingerprintshifts')->get();
        
        // الحصول على بيانات الموظفين
        $employees = DB::table('empinfo')->pluck('civilid')->toArray();
        
        // احتمالات التأخير أو التبكير (بالدقائق)
        $variations = [-20, -15, -10, -5, 0, 5, 10, 15, 20, 30];
        
        $id = 1; // معرف فريد للبصمة
        
        foreach ($shifts as $shift) {
            // لكل موظف في النوبة
            foreach ($employees as $civilid) {
                // 10% احتمال الغياب
                if (rand(1, 100) <= 90) {
                    $scheduledIn = Carbon::parse($shift->datetimein);
                    $scheduledOut = Carbon::parse($shift->dateout . ' ' . $shift->timeout);
                    
                    // =========== بصمات الدخول ===========
                    $mainInVariation = $variations[array_rand($variations)];
                    $actualInTime = $scheduledIn->copy()->addMinutes($mainInVariation);
                    
                    // بصمة الدخول الأساسية
                    DB::table('fingerprint24')->insert([
                        'id' => $id++,
                        'printtype' => 'f1',
                        'printtime' => $actualInTime->format('H:i:s'),
                        'printdate' => $actualInTime->format('Y-m-d'),
                        'civilid' => $civilid,
                        'masterid' => 1,
                        'userid' => 1
                    ]);

                    // 20% احتمال تكرار بصمة الدخول (غير متأكد)
                    if (rand(1, 100) <= 20) {
                        $repeatTime = $actualInTime->copy()->addMinutes(rand(1, 3));
                        DB::table('fingerprint24')->insert([
                            'id' => $id++,
                            'printtype' => 'f1',
                            'printtime' => $repeatTime->format('H:i:s'),
                            'printdate' => $repeatTime->format('Y-m-d'),
                            'civilid' => $civilid,
                            'masterid' => 1,
                            'userid' => 1
                        ]);
                    }

                    // 10% احتمال بصمة خروج بالخطأ عند الدخول
                    if (rand(1, 100) <= 10) {
                        $wrongTime = $actualInTime->copy()->addMinutes(rand(1, 5));
                        DB::table('fingerprint24')->insert([
                            'id' => $id++,
                            'printtype' => 'f2',
                            'printtime' => $wrongTime->format('H:i:s'),
                            'printdate' => $wrongTime->format('Y-m-d'),
                            'civilid' => $civilid,
                            'masterid' => 1,
                            'userid' => 1
                        ]);
                    }

                    // =========== بصمات الخروج ===========
                    $mainOutVariation = $variations[array_rand($variations)];
                    $actualOutTime = $scheduledOut->copy()->addMinutes($mainOutVariation);
                    
                    // بصمة الخروج الأساسية
                    DB::table('fingerprint24')->insert([
                        'id' => $id++,
                        'printtype' => 'f2',
                        'printtime' => $actualOutTime->format('H:i:s'),
                        'printdate' => $actualOutTime->format('Y-m-d'),
                        'civilid' => $civilid,
                        'masterid' => 1,
                        'userid' => 1
                    ]);

                    // 20% احتمال تكرار بصمة الخروج (غير متأكد)
                    if (rand(1, 100) <= 20) {
                        $repeatTime = $actualOutTime->copy()->addMinutes(rand(1, 3));
                        DB::table('fingerprint24')->insert([
                            'id' => $id++,
                            'printtype' => 'f2',
                            'printtime' => $repeatTime->format('H:i:s'),
                            'printdate' => $repeatTime->format('Y-m-d'),
                            'civilid' => $civilid,
                            'masterid' => 1,
                            'userid' => 1
                        ]);
                    }

                    // 10% احتمال بصمة دخول بالخطأ عند الخروج
                    if (rand(1, 100) <= 10) {
                        $wrongTime = $actualOutTime->copy()->addMinutes(rand(1, 5));
                        DB::table('fingerprint24')->insert([
                            'id' => $id++,
                            'printtype' => 'f1',
                            'printtime' => $wrongTime->format('H:i:s'),
                            'printdate' => $wrongTime->format('Y-m-d'),
                            'civilid' => $civilid,
                            'masterid' => 1,
                            'userid' => 1
                        ]);
                    }
                }
            }
        }
    }
}
