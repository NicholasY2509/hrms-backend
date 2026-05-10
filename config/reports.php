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
    ]
];
