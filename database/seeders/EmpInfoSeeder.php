<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpInfoSeeder extends Seeder
{
    public function run()
    {
        $employees = [
            [
                'civilid' => '1001234567',
                'filno' => '1001',
                'empname' => 'أحمد محمد العنزي',
                'secid' => '101',
                'hiredate' => '2023-01-15',
                'sex' => 'ذكر',
                'card' => 'A',
                'empid' => '1001'
            ],
            [
                'civilid' => '1001234568',
                'filno' => '1002',
                'empname' => 'فاطمة خالد السالم',
                'secid' => '102',
                'hiredate' => '2023-02-01',
                'sex' => 'أنثى',
                'card' => 'B',
                'empid' => '1002'
            ],
            [
                'civilid' => '1001234569',
                'filno' => '1003',
                'empname' => 'عبدالله سعد الدوسري',
                'secid' => '101',
                'hiredate' => '2023-03-10',
                'sex' => 'ذكر',
                'card' => 'A',
                'empid' => '1003'
            ],
            [
                'civilid' => '1001234570',
                'filno' => '1004',
                'empname' => 'نورة علي المطيري',
                'secid' => '103',
                'hiredate' => '2023-04-05',
                'sex' => 'أنثى',
                'card' => 'B',
                'empid' => '1004'
            ],
            [
                'civilid' => '1001234571',
                'filno' => '1005',
                'empname' => 'خالد عبدالرحمن العتيبي',
                'secid' => '102',
                'hiredate' => '2023-05-20',
                'sex' => 'ذكر',
                'card' => 'A',
                'empid' => '1005'
            ],
            [
                'civilid' => '1001234572',
                'filno' => '1006',
                'empname' => 'سارة محمد الشمري',
                'secid' => '103',
                'hiredate' => '2023-06-15',
                'sex' => 'أنثى',
                'card' => 'B',
                'empid' => '1006'
            ],
            [
                'civilid' => '1001234573',
                'filno' => '1007',
                'empname' => 'فهد ناصر القحطاني',
                'secid' => '101',
                'hiredate' => '2023-07-01',
                'sex' => 'ذكر',
                'card' => 'A',
                'empid' => '1007'
            ],
            [
                'civilid' => '1001234574',
                'filno' => '1008',
                'empname' => 'منيرة سعد الرشيدي',
                'secid' => '102',
                'hiredate' => '2023-08-10',
                'sex' => 'أنثى',
                'card' => 'B',
                'empid' => '1008'
            ],
            [
                'civilid' => '1001234575',
                'filno' => '1009',
                'empname' => 'بدر طلال السبيعي',
                'secid' => '103',
                'hiredate' => '2023-09-01',
                'sex' => 'ذكر',
                'card' => 'A',
                'empid' => '1009'
            ],
            [
                'civilid' => '1001234576',
                'filno' => '1010',
                'empname' => 'عهود فيصل الحربي',
                'secid' => '101',
                'hiredate' => '2023-10-15',
                'sex' => 'أنثى',
                'card' => 'B',
                'empid' => '1010'
            ]
        ];

        foreach ($employees as $employee) {
            DB::table('empinfo')->insert($employee);
        }
    }
}
