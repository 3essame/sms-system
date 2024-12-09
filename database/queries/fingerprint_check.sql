-- عدد البصمات لكل موظف
SELECT 
    f.civilid,
    e.empname,
    COUNT(*) as total_prints,
    SUM(CASE WHEN f.printtype = 'f1' THEN 1 ELSE 0 END) as checkin_count,
    SUM(CASE WHEN f.printtype = 'f2' THEN 1 ELSE 0 END) as checkout_count
FROM fingerprint24 f
JOIN empinfo e ON e.civilid = f.civilid
GROUP BY f.civilid, e.empname;

-- التحقق من أوقات البصمات مقارنة بالنوبات
SELECT 
    f.civilid,
    e.empname,
    e.card as assigned_shift,
    f.printdate,
    f.printtime,
    f.printtype,
    s.timein as shift_start,
    s.timeout as shift_end,
    s.shift as shift_type,
    s.shifttype as shift_category
FROM fingerprint24 f
INNER JOIN empinfo e ON e.civilid = f.civilid
INNER JOIN fingerprintshifts s 
    ON DATE(f.printdate) = DATE(s.datetimein)
    AND e.card = s.shift  -- Matching employee's assigned shift with the shift schedule
WHERE f.printdate = '2023-12-01'
    AND f.printtype IN ('f1', 'f2')  -- Valid print types only
ORDER BY 
    f.civilid,
    f.printdate,
    f.printtime;

-- التحقق من البصمات المتكررة
SELECT 
    civilid,
    printdate,
    printtype,
    COUNT(*) as print_count,
    GROUP_CONCAT(printtime ORDER BY printtime) as print_times
FROM fingerprint24
GROUP BY civilid, printdate, printtype
HAVING COUNT(*) > 1
ORDER BY printdate, civilid;

-- تحديد فترة التقرير
SET @start_date = '2023-12-01';  -- تاريخ بداية الفترة
SET @end_date = '2023-12-31';    -- تاريخ نهاية الفترة

WITH date_range AS (
    -- إنشاء جدول بكل التواريخ في الفترة المحددة
    SELECT date_list.date as check_date
    FROM (
        SELECT 
            DATE_ADD(@start_date, INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY) as date
        FROM (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as a
        CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as b
        CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as c
    ) date_list
    WHERE date_list.date BETWEEN @start_date AND @end_date
),
employee_shifts AS (
    -- الحصول على معلومات النوبات للموظفين لكل يوم في الفترة
    SELECT 
        e.civilid,
        e.empname,
        e.card as assigned_shift,
        s.shifttype,
        s.timein as scheduled_in,
        s.timeout as scheduled_out,
        s.shift as shift_type,
        s.datetimein as shift_datetime,
        dr.check_date as work_date
    FROM empinfo e
    CROSS JOIN date_range dr
    INNER JOIN fingerprintshifts s 
        ON e.card = s.shift
        AND DATE(s.datetimein) = dr.check_date
),
fingerprint_logs AS (
    -- تجميع سجلات البصمات
    SELECT 
        es.*,
        -- وقت أول بصمة دخول
        MIN(CASE WHEN f.printtype = 'f1' THEN STR_TO_DATE(CONCAT(f.printdate, ' ', f.printtime), '%Y-%m-%d %H:%i:%s') END) as actual_in_datetime,
        -- وقت آخر بصمة خروج
        MAX(CASE WHEN f.printtype = 'f2' THEN STR_TO_DATE(CONCAT(f.printdate, ' ', f.printtime), '%Y-%m-%d %H:%i:%s') END) as actual_out_datetime,
        -- توقيت الدخول والخروج للعرض
        DATE_FORMAT(MIN(CASE WHEN f.printtype = 'f1' THEN STR_TO_DATE(CONCAT(f.printdate, ' ', f.printtime), '%Y-%m-%d %H:%i:%s') END), '%H:%i') as display_in_time,
        DATE_FORMAT(MAX(CASE WHEN f.printtype = 'f2' THEN STR_TO_DATE(CONCAT(f.printdate, ' ', f.printtime), '%Y-%m-%d %H:%i:%s') END), '%H:%i') as display_out_time
    FROM employee_shifts es
    LEFT JOIN fingerprint24 f 
        ON es.civilid = f.civilid 
        AND f.printdate = es.work_date
    GROUP BY 
        es.civilid, 
        es.empname, 
        es.assigned_shift,
        es.shifttype,
        es.scheduled_in,
        es.scheduled_out,
        es.shift_type,
        es.shift_datetime,
        es.work_date
)
SELECT 
    -- معلومات الموظف الأساسية
    fl.civilid as 'رقم الموظف',
    fl.empname as 'اسم الموظف',
    DATE_FORMAT(fl.work_date, '%d/%m/%Y') as 'التاريخ',
    fl.shift_type as 'النوبة',
    CASE fl.shifttype 
        WHEN 'm' THEN 'صباحي'
        WHEN 'n' THEN 'ليلي'
        ELSE 'غير محدد'
    END as 'نوع النوبة',
    
    -- أوقات الدوام المجدولة
    TIME_FORMAT(fl.scheduled_in, '%H:%i') as 'وقت الدخول المجدول',
    TIME_FORMAT(fl.scheduled_out, '%H:%i') as 'وقت الخروج المجدول',
    
    -- أوقات الحضور الفعلية
    fl.display_in_time as 'وقت الدخول الفعلي',
    fl.display_out_time as 'وقت الخروج الفعلي',
    
    -- حالة الحضور
    CASE 
        WHEN fl.actual_in_datetime IS NULL AND fl.actual_out_datetime IS NULL THEN 'غائب'
        WHEN fl.actual_in_datetime IS NULL THEN 'لم يسجل دخول'
        WHEN fl.actual_out_datetime IS NULL THEN 'لم يسجل خروج'
        ELSE 'حاضر'
    END as 'حالة الحضور',
    
    -- حالة الدخول والخروج
    CASE 
        WHEN fl.actual_in_datetime IS NULL THEN NULL
        WHEN fl.actual_in_datetime < fl.shift_datetime THEN 'مبكر'
        WHEN fl.actual_in_datetime > fl.shift_datetime THEN 'متأخر'
        ELSE 'في الوقت'
    END as 'حالة الدخول',
    
    CASE 
        WHEN fl.actual_out_datetime IS NULL THEN NULL
        WHEN fl.actual_out_datetime < DATE_ADD(fl.shift_datetime, INTERVAL 12 HOUR) THEN 'خروج مبكر'
        WHEN fl.actual_out_datetime > DATE_ADD(fl.shift_datetime, INTERVAL 12 HOUR) THEN 'تجاوز وقت'
        ELSE 'في الوقت'
    END as 'حالة الخروج',
    
    -- حساب مدة التأخير والمغادرة المبكرة
    CASE 
        WHEN fl.actual_in_datetime IS NULL THEN '00:00'
        WHEN fl.actual_in_datetime <= fl.shift_datetime THEN '00:00'
        ELSE TIME_FORMAT(
                TIMEDIFF(
                    fl.actual_in_datetime,
                    fl.shift_datetime
                ),
                '%H:%i'
            )
    END as 'مدة التأخير',
    
    CASE 
        WHEN fl.actual_out_datetime IS NULL THEN '00:00'
        WHEN fl.actual_out_datetime >= DATE_ADD(fl.shift_datetime, INTERVAL 12 HOUR) THEN '00:00'
        ELSE TIME_FORMAT(
                TIMEDIFF(
                    DATE_ADD(fl.shift_datetime, INTERVAL 12 HOUR),
                    fl.actual_out_datetime
                ),
                '%H:%i'
            )
    END as 'مدة المغادرة المبكرة',
    
    -- حساب ساعات العمل والوقت الإضافي
    CASE 
        WHEN fl.actual_in_datetime IS NULL OR fl.actual_out_datetime IS NULL THEN '00:00'
        ELSE TIME_FORMAT(
                TIMEDIFF(
                    CASE 
                        WHEN fl.actual_out_datetime > DATE_ADD(fl.shift_datetime, INTERVAL 12 HOUR) 
                        THEN DATE_ADD(fl.shift_datetime, INTERVAL 12 HOUR)
                        ELSE fl.actual_out_datetime 
                    END,
                    CASE 
                        WHEN fl.actual_in_datetime < fl.shift_datetime 
                        THEN fl.shift_datetime
                        ELSE fl.actual_in_datetime 
                    END
                ),
                '%H:%i'
            )
    END as 'ساعات العمل الفعلية',
    
    CASE 
        WHEN fl.actual_out_datetime IS NULL THEN '00:00'
        WHEN fl.actual_out_datetime <= DATE_ADD(fl.shift_datetime, INTERVAL 12 HOUR) THEN '00:00'
        ELSE TIME_FORMAT(
                TIMEDIFF(
                    fl.actual_out_datetime,
                    DATE_ADD(fl.shift_datetime, INTERVAL 12 HOUR)
                ),
                '%H:%i'
            )
    END as 'الوقت الإضافي'
FROM fingerprint_logs fl
ORDER BY 
    fl.civilid,
    fl.work_date;
