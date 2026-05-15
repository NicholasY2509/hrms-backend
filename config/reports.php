<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report Type Mapping
    |--------------------------------------------------------------------------
    |
    | This map defines which Export class and PDF view should be used for 
    | each report type. Adding a new report is as simple as adding an 
    | entry to this array.
    |
    */
    'map' => [
        'employee_list' => [
            'class' => \App\Modules\Employee\Exports\EmployeeExport::class,
            'view' => 'exports.employee.employee_pdf',
        ],
        'daily_report' => [
            'class' => \App\Modules\Attendance\Exports\DailyAttendanceExport::class,
            'view' => 'exports.attendance.daily_report_pdf',
        ],
        'personal_report' => [
            'class' => \App\Modules\Attendance\Exports\PersonalAttendanceExport::class,
            'view' => 'exports.attendance.personal_report_pdf',
        ],
        'team_report' => [
            'class' => \App\Modules\Attendance\Exports\TeamAttendanceExport::class,
            'view' => 'exports.attendance.team_report_pdf',
            'txt_view' => 'exports.attendance.team_report_txt',
        ],
        'certificate_of_employment' => [
            'class' => \App\Modules\CertificateOfEmployment\Exports\CertificateOfEmploymentExport::class,
            'view' => 'exports.certificate_of_employment.coe_pdf',
        ],
        'warning_letter' => [
            'class' => \App\Modules\Disciplinary\Exports\WarningLetterExport::class,
            'view' => 'exports.disciplinary.warning_letter_pdf',
        ],
        'resignation' => [
            'class' => \App\Modules\Employee\Exports\ResignationExport::class,
            'view' => 'exports.employee.resignation_pdf',
        ],
        'career' => [
            'class' => \App\Modules\Career\Exports\CareerExport::class,
            'view' => 'exports.career.career_pdf',
        ],
        'overtime' => [
            'class' => \App\Modules\Overtime\Exports\OvertimeExport::class,
            'view' => 'exports.overtime.overtime_pdf',
        ],
    ]
];
