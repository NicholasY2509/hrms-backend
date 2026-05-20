<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Repositories\EmployeeRepository;
use App\Modules\Employee\Models\EmployeeAttachment;
use App\Modules\Employee\Models\UserEmployee;
use App\Modules\Organization\Models\WorkPosition;
use App\Modules\Payroll\Models\TaxPtkpSetting;
use App\Modules\User\Models\User;
use App\Services\StorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeService
{
    protected EmployeeRepository $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Get the profile for a specific user ID.
     *
     * @param int $userId
     * @return Employee|null
     */
    public function getProfile(int $userId): ?Employee
    {
        return $this->employeeRepository->findByUserId($userId);
    }

    /**
     * List employees with pagination and filters.
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listEmployees(int $perPage = 15, array $filters = [])
    {
        return $this->employeeRepository->paginate($perPage, $filters);
    }

    /**
     * Get summary of employees by status.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEmployeeSummary()
    {
        return $this->employeeRepository->getSummary();
    }

    /**
     * Get an employee by ID.
     *
     * @param int $id
     * @return Employee
     */
    public function getEmployee(int $id): Employee
    {
        return $this->employeeRepository->findById($id);
    }

    public function createEmployee(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            // 1. Formatting - Uppercase relevant fields as per legacy HRMS
            if (isset($data['first_name'])) $data['first_name'] = Str::upper($data['first_name']);
            if (isset($data['last_name'])) $data['last_name'] = Str::upper($data['last_name']);
            if (isset($data['full_name'])) {
                $data['full_name'] = Str::upper($data['full_name']);
            } else {
                $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            }
            if (isset($data['current_address'])) $data['current_address'] = Str::upper($data['current_address']);
            if (isset($data['residence_address'])) $data['residence_address'] = Str::upper($data['residence_address']);
            if (isset($data['place_birth'])) $data['place_birth'] = Str::upper($data['place_birth']);

            // 2. Set Default Status to Active (1)
            $data['work_employee_status_id'] = 1;

            // 3. Handle Avatar Upload
            if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                $data['avatar'] = StorageService::store($data['avatar'], 'avatars');
            }

            // 4. Separate Employee data from User/Attachment data
            $userData = [
                'email' => $data['email'],
                'password' => $data['password'],
                'full_name' => $data['full_name'],
            ];

            $attachmentData = [
                'ktp' => $data['ktp'] ?? null,
                'kartu_keluarga' => $data['kartu_keluarga'] ?? null,
                'ijazah' => $data['ijazah'] ?? null,
                'file_pendukung' => $data['file_pendukung'] ?? [],
            ];

            // Remove non-employee columns
            $employeeData = collect($data)->except([
                'email', 'password', 'ktp', 'kartu_keluarga', 'ijazah', 'file_pendukung'
            ])->toArray();

            // 5. Create Employee Record
            $employee = $this->employeeRepository->create($employeeData);

            // 6. Create User Account
            $user = User::create([
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);

            // 7. Link Employee to User
            UserEmployee::create([
                'user_id' => $user->id,
                'employee_id' => $employee->id,
            ]);

            // 8. Handle Attachments (KTP, KK, Ijazah, Supporting Files)
            $this->handleAttachments($employee, $attachmentData);

            // 8. Passport API Sync Placeholder
            // TODO: Setup API endpoint to create a new user in Passport system
            /*
            $passportData = [
                'employee_id_number' => $employee->employee_id_number,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'full_name' => $employee->full_name,
                'email' => $user->email,
                'password' => $data['password'], // Raw password for passport sync
            ];
            // $this->passportApiService->createUser($passportData);
            */

            return $employee->load(['user_employee.user', 'attachments']);
        });
    }

    /**
     * Handle employee attachments.
     */
    protected function handleAttachments(Employee $employee, array $data): void
    {
        $attachmentTypes = [
            'ktp' => 'KTP',
            'kartu_keluarga' => 'Kartu Keluarga',
            'ijazah' => 'Ijazah',
        ];

        foreach ($attachmentTypes as $key => $label) {
            if (isset($data[$key]) && $data[$key] instanceof \Illuminate\Http\UploadedFile) {
                $path = StorageService::store($data[$key], 'employee_attachments');
                EmployeeAttachment::create([
                    'employee_id' => $employee->id,
                    'name' => $label,
                    'path' => $path,
                ]);
            }
        }

        if (isset($data['file_pendukung']) && is_array($data['file_pendukung'])) {
            foreach ($data['file_pendukung'] as $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $path = StorageService::store($file, 'employee_attachments');
                    EmployeeAttachment::create([
                        'employee_id' => $employee->id,
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                    ]);
                }
            }
        }
    }

    /**
     * Update an employee.
     *
     * @param int $id
     * @param array $data
     * @return Employee
     */
    public function updateEmployee(int $id, array $data): Employee
    {
        // Business logic for update can go here
        return $this->employeeRepository->update($id, $data);
    }

    /**
     * Update employee details by type.
     */
    public function updateDetail(int $id, string $type, array $data, array $config): Employee
    {
        $employee = $this->employeeRepository->findById($id);

        if ($type === 'insurance') {
            // Specifically handle insurance type to update both Employee fields and insurances relationship
            
            // 1. Update Employee table fields if present
            $employeeUpdates = [];
            if (isset($data['is_bpjs_ketenagakerjaan'])) {
                $employeeUpdates['is_bpjs_ketenagakerjaan'] = $data['is_bpjs_ketenagakerjaan'];
            }
            if (isset($data['is_bpjs_kesehatan'])) {
                $employeeUpdates['is_bpjs_kesehatan'] = $data['is_bpjs_kesehatan'];
            }
            if (!empty($employeeUpdates)) {
                $employee->update($employeeUpdates);
            }

            // 2. Extract the insurance records array (support both 'insurances' and 'insurance' keys)
            $insuranceRecords = null;
            if (isset($data['insurances'])) {
                $insuranceRecords = $data['insurances'];
            } elseif (isset($data['insurance'])) {
                $insuranceRecords = $data['insurance'];
            }

            // If the incoming payload itself is a flat array, treat it as the insurance records list
            if (is_array($data) && isset($data[0]) && is_array($data[0])) {
                $insuranceRecords = $data;
            }

            // 3. Update the insurances relationship if records are provided
            if (!is_null($insuranceRecords)) {
                $incomingIds = collect($insuranceRecords)->pluck('id')->filter()->toArray();
                
                // Delete items not in incoming payload
                $employee->insurances()->whereNotIn('id', $incomingIds)->delete();
                
                // Update or Create
                foreach ($insuranceRecords as $item) {
                    if (isset($item['id'])) {
                        $detailId = $item['id'];
                        unset($item['id']);
                        $employee->insurances()->where('id', $detailId)->update($item);
                    } else {
                        $employee->insurances()->create($item);
                    }
                }
            }

            return $employee->fresh();
        }

        // Unwrap data if it's nested under the type or relation key (from validated request)
        if (isset($data[$type])) {
            $data = $data[$type];
        } elseif (isset($config['relation']) && isset($data[$config['relation']])) {
            $data = $data[$config['relation']];
        }

        if (isset($config['is_relation']) && $config['is_relation'] === false) {
            // Map virtual fields to DB columns
            if (isset($data['birth_place'])) {
                $data['place_birth'] = $data['birth_place'];
                unset($data['birth_place']);
            }
            if (isset($data['birth_date'])) {
                $data['date_birth'] = $data['birth_date'];
                unset($data['birth_date']);
            }
            if (isset($data['is_get_annual_leaves'])) {
                $data['is_get_annual_leave'] = $data['is_get_annual_leaves'];
                unset($data['is_get_annual_leaves']);
            }

            // Handle user email update if provided
            if (isset($data['email'])) {
                $user = $employee->user;
                if ($user) {
                    $user->update(['email' => $data['email']]);
                }
                unset($data['email']);
            }

            $employee->update($data);
        } else {
            $relationship = $config['relation'];
            $isAttachment = $relationship === 'attachments';
            $isSingle = isset($config['is_single']) && $config['is_single'] === true;

            if ($isSingle) {
                if ($type === 'tax-profile' && isset($data['ptkp_status'])) {
                    $ptkp = TaxPtkpSetting::where('code', $data['ptkp_status'])->first();
                    if ($ptkp) {
                        $data['ptkp_setting_id'] = $ptkp->id;
                    }
                    unset($data['ptkp_status']);
                    unset($data['ter_category']); // Derived from ptkp_setting
                }

                $employee->{$relationship}()->updateOrCreate([], $data);
            } elseif (isset($data[0]) && is_array($data[0])) {
                $incomingIds = collect($data)->pluck('id')->filter()->toArray();
                
                // 1. Delete items not in incoming payload
                $employee->{$relationship}()->whereNotIn('id', $incomingIds)->delete();
                
                // 2. Update or Create
                foreach ($data as $item) {
                    if ($isAttachment && isset($item['file']) && $item['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $path = StorageService::store($item['file'], 'employee_attachments');
                        $item['path'] = $path;
                        $item['name'] = $item['name'] ?? $item['file']->getClientOriginalName();
                        unset($item['file']);
                    }

                    if (isset($item['id'])) {
                        $detailId = $item['id'];
                        unset($item['id']);
                        $employee->{$relationship}()->where('id', $detailId)->update($item);
                    } else {
                        $employee->{$relationship}()->create($item);
                    }
                }
            } else {
                // Single item fallback
                if (isset($data['id'])) {
                    $detailId = $data['id'];
                    unset($data['id']);
                    $employee->{$relationship}()->where('id', $detailId)->update($data);
                } else {
                    $employee->{$relationship}()->create($data);
                }
            }
        }

        return $employee->fresh();
    }

    /**
     * Delete an employee.
     *
     * @param int $id
     * @return bool
     */
    public function deleteEmployee(int $id): bool
    {
        // Business logic for deletion can go here
        return $this->employeeRepository->delete($id);
    }

    /**
     * Generate a new employee ID number based on work position.
     *
     * @param int $workPositionId
     * @return string
     */
    public function generateEmployeeIdNumber(int $workPositionId): string
    {
        $workPosition = WorkPosition::findOrFail($workPositionId);

        if ($workPosition->prefix) {
            $employee = $this->employeeRepository->getLastEmployeeByWorkPosition($workPosition->id);
        } else {
            $excludeWorkPositions = WorkPosition::whereNotNull('prefix')->pluck('id')->toArray();
            $employee = $this->employeeRepository->getLastEmployeeExcludingWorkPositions($excludeWorkPositions);
        }

        if ($workPosition->prefix) {
            $sequence = $employee ? (int) str_replace($workPosition->prefix, '', $employee->employee_id_number) : 0;
        } else {
            if ($employee) {
                $numericPart = preg_replace('/[^0-9]/', '', $employee->employee_id_number);
                $sequence = (int) $numericPart;
            } else {
                $sequence = 0;
            }
        }

        $sequence++;

        return ($workPosition->prefix ?? '') . sprintf("%06s", $sequence);
    }
}
