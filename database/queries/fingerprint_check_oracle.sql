-- تحديد فترة التقرير
VARIABLE start_date DATE;
VARIABLE end_date DATE;
EXEC :start_date := DATE '2023-12-01';
EXEC :end_date := DATE '2023-12-31';

WITH date_range AS (
    -- إنشاء جدول بكل التواريخ في الفترة المحددة
    SELECT TRUNC(:start_date + LEVEL - 1) as check_date
    FROM DUAL
    CONNECT BY LEVEL <= (:end_date - :start_date + 1)
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
        AND TRUNC(s.datetimein) = dr.check_date
),
fingerprint_logs AS (
    -- تجميع سجلات البصمات
    SELECT 
        es.*,
        -- وقت أول بصمة دخول
        MIN(CASE WHEN f.printtype = 'f1' THEN 
            TO_TIMESTAMP(TO_CHAR(f.printdate, 'YYYY-MM-DD') || ' ' || f.printtime, 'YYYY-MM-DD HH24:MI:SS')
        END) as actual_in_datetime,
        -- وقت آخر بصمة خروج
        MAX(CASE WHEN f.printtype = 'f2' THEN 
            TO_TIMESTAMP(TO_CHAR(f.printdate, 'YYYY-MM-DD') || ' ' || f.printtime, 'YYYY-MM-DD HH24:MI:SS')
        END) as actual_out_datetime,
        -- توقيت الدخول والخروج للعرض
        TO_CHAR(MIN(CASE WHEN f.printtype = 'f1' THEN 
            TO_TIMESTAMP(TO_CHAR(f.printdate, 'YYYY-MM-DD') || ' ' || f.printtime, 'YYYY-MM-DD HH24:MI:SS')
        END), 'HH24:MI') as display_in_time,
        TO_CHAR(MAX(CASE WHEN f.printtype = 'f2' THEN 
            TO_TIMESTAMP(TO_CHAR(f.printdate, 'YYYY-MM-DD') || ' ' || f.printtime, 'YYYY-MM-DD HH24:MI:SS')
        END), 'HH24:MI') as display_out_time
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
    fl.civilid as "رقم الموظف",
    fl.empname as "اسم الموظف",
    TO_CHAR(fl.work_date, 'DD/MM/YYYY') as "التاريخ",
    fl.shift_type as "النوبة",
    CASE fl.shifttype 
        WHEN 'm' THEN 'صباحي'
        WHEN 'n' THEN 'ليلي'
        ELSE 'غير محدد'
    END as "نوع النوبة",
    
    -- أوقات الدوام المجدولة
    TO_CHAR(TO_TIMESTAMP(fl.scheduled_in, 'HH24:MI:SS'), 'HH24:MI') as "وقت الدخول المجدول",
    TO_CHAR(TO_TIMESTAMP(fl.scheduled_out, 'HH24:MI:SS'), 'HH24:MI') as "وقت الخروج المجدول",
    
    -- أوقات الحضور الفعلية
    fl.display_in_time as "وقت الدخول الفعلي",
    fl.display_out_time as "وقت الخروج الفعلي",
    
    -- حالة الحضور
    CASE 
        WHEN fl.actual_in_datetime IS NULL AND fl.actual_out_datetime IS NULL THEN 'غائب'
        WHEN fl.actual_in_datetime IS NULL THEN 'لم يسجل دخول'
        WHEN fl.actual_out_datetime IS NULL THEN 'لم يسجل خروج'
        ELSE 'حاضر'
    END as "حالة الحضور",
    
    -- حالة الدخول والخروج
    CASE 
        WHEN fl.actual_in_datetime IS NULL THEN NULL
        WHEN fl.actual_in_datetime < fl.shift_datetime THEN 'مبكر'
        WHEN fl.actual_in_datetime > fl.shift_datetime THEN 'متأخر'
        ELSE 'في الوقت'
    END as "حالة الدخول",
    
    CASE 
        WHEN fl.actual_out_datetime IS NULL THEN NULL
        WHEN fl.actual_out_datetime < fl.shift_datetime + INTERVAL '12' HOUR THEN 'خروج مبكر'
        WHEN fl.actual_out_datetime > fl.shift_datetime + INTERVAL '12' HOUR THEN 'تجاوز وقت'
        ELSE 'في الوقت'
    END as "حالة الخروج",
    
    -- حساب مدة التأخير والمغادرة المبكرة
    CASE 
        WHEN fl.actual_in_datetime IS NULL THEN '00:00'
        WHEN fl.actual_in_datetime <= fl.shift_datetime THEN '00:00'
        ELSE TO_CHAR(
                (fl.actual_in_datetime - fl.shift_datetime) * 24,
                'HH24:MI'
            )
    END as "مدة التأخير",
    
    CASE 
        WHEN fl.actual_out_datetime IS NULL THEN '00:00'
        WHEN fl.actual_out_datetime >= fl.shift_datetime + INTERVAL '12' HOUR THEN '00:00'
        ELSE TO_CHAR(
                ((fl.shift_datetime + INTERVAL '12' HOUR) - fl.actual_out_datetime) * 24,
                'HH24:MI'
            )
    END as "مدة المغادرة المبكرة",
    
    -- حساب ساعات العمل والوقت الإضافي
    CASE 
        WHEN fl.actual_in_datetime IS NULL OR fl.actual_out_datetime IS NULL THEN '00:00'
        ELSE TO_CHAR(
                (
                    CASE 
                        WHEN fl.actual_out_datetime > fl.shift_datetime + INTERVAL '12' HOUR 
                        THEN fl.shift_datetime + INTERVAL '12' HOUR
                        ELSE fl.actual_out_datetime 
                    END
                    -
                    CASE 
                        WHEN fl.actual_in_datetime < fl.shift_datetime 
                        THEN fl.shift_datetime
                        ELSE fl.actual_in_datetime 
                    END
                ) * 24,
                'HH24:MI'
            )
    END as "ساعات العمل الفعلية",
    
    CASE 
        WHEN fl.actual_out_datetime IS NULL THEN '00:00'
        WHEN fl.actual_out_datetime <= fl.shift_datetime + INTERVAL '12' HOUR THEN '00:00'
        ELSE TO_CHAR(
                (fl.actual_out_datetime - (fl.shift_datetime + INTERVAL '12' HOUR)) * 24,
                'HH24:MI'
            )
    END as "الوقت الإضافي"
FROM fingerprint_logs fl
ORDER BY 
    fl.civilid,
    fl.work_date;
