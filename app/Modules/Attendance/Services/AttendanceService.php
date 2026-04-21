<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\Attendance\Models\Attendance;

use App\Exceptions\ApplicationException;
use App\Modules\Employee\Models\UserFaceProfile;
use App\Services\StorageService;
use App\Modules\Attendance\Models\AttendanceLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    protected AttendanceRepository $attendanceRepository;
    protected \App\Modules\Employee\Services\FaceRecognitionService $faceService;

    const STATUS_LATE = 9;
    const STATUS_PRESENT = 12;

    public function __construct(
        AttendanceRepository $attendanceRepository,
        \App\Modules\Employee\Services\FaceRecognitionService $faceService
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->faceService = $faceService;
    }

    /**
     * Clock in the user.
     */
    public function clockIn(int $userId, array $data): Attendance
    {
        $faceProfile = UserFaceProfile::where('user_id', $userId)->first();
        if (!$faceProfile) {
            throw new ApplicationException('Anda belum mendaftarkan wajah! Silahkan daftar di menu Profil.', 400);
        }

        $now = Carbon::now();
        $date = $now->format('Y-m-d');
        $time = $now->format('H:i:00');

        $workingHour = $this->attendanceRepository->getWorkingHourByUserId($userId, $date);
        if (!$workingHour) {
            throw new ApplicationException('Data Jam Kerja tidak ditemukan!', 400);
        }

        $attendance = $this->attendanceRepository->getAttendanceByWorkingHourId($workingHour->id);
        if ($attendance && $attendance->incoming_scan) {
            throw new ApplicationException('Anda sudah melakukan absensi masuk!', 400);
        }

        $location = $this->validateLocation($userId, $data['latitude'], $data['longitude']);
        if (!$location) {
            throw new ApplicationException('Lokasi anda terlalu jauh!', 400);
        }

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->attendance_working_hour_id = $workingHour->id;
        }

        $attendance->incoming_scan = $time;
        $attendance->incoming_latitude = $data['latitude'];
        $attendance->incoming_longitude = $data['longitude'];
        $attendance->incoming_location_id = $location->id;

        if (isset($data['photo'])) {
            $attendance->incoming_photo = StorageService::store($data['photo'], 'attendances');
        }

        $clockInTime = Carbon::parse($workingHour->working_hour->clock_in);
        $currentTime = Carbon::parse($time);

        if ($currentTime->greaterThan($clockInTime)) {
            $attendance->late_time = $currentTime->diff($clockInTime)->format('%H:%I:%S');
            $attendance->attendance_status_id = self::STATUS_LATE;
        } else {
            $attendance->attendance_status_id = self::STATUS_PRESENT;
        }

        $this->attendanceRepository->save($attendance);

        return $attendance->load(['attendance_status', 'attendance_working_hour.working_hour']);
    }

    /**
     * Clock out the user.
     */
    public function clockOut(int $userId, array $data): Attendance
    {
        // Face Verification (Mandatory)
        $faceProfile = UserFaceProfile::where('user_id', $userId)->first();
        if (!$faceProfile) {
            throw new ApplicationException('Anda belum mendaftarkan wajah! Silahkan daftar di menu Profil.', 400);
        }

        $now = Carbon::now();
        $date = $now->format('Y-m-d');
        $time = $now->format('H:i:00');

        // Legacy "batas" logic
        // $batas = Carbon::parse('10:30:00');
        // if ($now->lessThan($batas)) {
        //     throw new ApplicationException('Anda belum bisa melakukan absensi pulang!', 400);
        // }

        $workingHour = $this->attendanceRepository->getWorkingHourByUserId($userId, $date);
        if (!$workingHour) {
            throw new ApplicationException('Data Jam Kerja tidak ditemukan!', 400);
        }

        $attendance = $this->attendanceRepository->getAttendanceByWorkingHourId($workingHour->id);
        if (!$attendance || !$attendance->incoming_scan) {
            throw new ApplicationException('Anda belum melakukan absensi masuk!', 400);
        }

        if ($attendance->outgoing_scan) {
            throw new ApplicationException('Anda sudah melakukan absensi pulang!', 400);
        }

        $location = $this->validateLocation($userId, $data['latitude'], $data['longitude']);
        if (!$location) {
            throw new ApplicationException('Lokasi anda terlalu jauh!', 400);
        }

        $attendance->outgoing_scan = $time;
        $attendance->outgoing_latitude = $data['latitude'];
        $attendance->outgoing_longitude = $data['longitude'];
        $attendance->outgoing_location_id = $location->id;

        if (isset($data['photo'])) {
            $attendance->outgoing_photo = StorageService::store($data['photo'], 'attendances');
        }

        $clockOutTime = Carbon::parse($workingHour->working_hour->clock_out);
        $currentTime = Carbon::parse($time);

        if ($clockOutTime->greaterThan($currentTime)) {
            $attendance->early_time = $clockOutTime->diff($currentTime)->format('%H:%I:%S');
        }

        $this->attendanceRepository->save($attendance);

        return $attendance->load(['attendance_status', 'attendance_working_hour.working_hour']);
    }

    /**
     * Validate user location against registered attendance locations.
     */
    protected function validateLocation(int $userId, float $lat, float $lon): ?AttendanceLocation
    {
        $locations = $this->attendanceRepository->getValidLocationsByUserId($userId);

        foreach ($locations as $location) {
            $distance = $this->calculateDistance($location->latitude, $location->longitude, $lat, $lon);
            if ($distance <= $location->distance) {
                return $location;
            }
        }

        return null;
    }

    /**
     * Haversine formula to calculate distance between two points.
     */
    protected function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c);
    }

    /**
     * Get the attendance status for the authenticated user.
     */
    public function getUserStatus(int $userId): ?Attendance
    {
        $attendance = $this->attendanceRepository->getStatusByUserId($userId);

        if (!$attendance) {
            $date = Carbon::now()->format('Y-m-d');
            $workingHour = $this->attendanceRepository->getWorkingHourByUserId($userId, $date);

            if ($workingHour) {
                $attendance = new Attendance();
                $attendance->setRelation('attendance_working_hour', $workingHour);
            }
        }

        return $attendance;
    }

    /**
     * Get working hour for a user by date.
     */
    public function getWorkingHourByDate(int $userId, string $date): ?\App\Modules\Attendance\Models\AttendanceWorkingHour
    {
        return $this->attendanceRepository->getWorkingHourByUserId($userId, $date);
    }

    /**
     * Get attendance history and summary for a user.
     */
    public function getHistoryWithSummary(int $userId, array $params): array
    {
        $startDate = $params['start_date'];
        $endDate = $params['end_date'];

        $records = $this->attendanceRepository->getHistory($userId, $startDate, $endDate);
        $summary = $this->attendanceRepository->getSummary($userId, $startDate, $endDate);

        return [
            'records' => $records,
            'summary' => $summary,
        ];
    }

    /**
     * Get attendance summary for a user.
     */
    public function getSummary(int $userId, string $startDate, string $endDate)
    {
        return $this->attendanceRepository->getSummary($userId, $startDate, $endDate);
    }

    /**
     * Check if a user's current location is valid.
     */
    public function checkUserLocation(int $userId, float $lat, float $lon): bool
    {
        return $this->validateLocation($userId, $lat, $lon) !== null;
    }
}

