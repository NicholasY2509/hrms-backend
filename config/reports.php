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
            'view' => 'exports.employee_pdf',
        ],
        'attendance_report' => [
            'class' => \App\Modules\Attendance\Exports\AttendanceExport::class,
            'view' => 'exports.attendance_pdf',
        ],
    ]
];
