<?php

namespace App\Services;

use App\Models\Staff;
use App\Repositories\StaffRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StaffService
{
    public function __construct(private readonly StaffRepository $staffRepository) {}

    public function list(int $perPage = 10): LengthAwarePaginator
    {
        return $this->staffRepository->paginate($perPage);
    }

    public function create(array $data): Staff
    {
        if (isset($data['signature']) && $data['signature'] instanceof UploadedFile) {
            $data['signature'] = $data['signature']->store('staff-signatures', 'public');
        }

        return $this->staffRepository->create($data);
    }

    public function update(Staff $staff, array $data): Staff
    {
        if (isset($data['signature']) && $data['signature'] instanceof UploadedFile) {
            if ($staff->signature) {
                Storage::disk('public')->delete($staff->signature);
            }
            $data['signature'] = $data['signature']->store('staff-signatures', 'public');
        }

        return $this->staffRepository->update($staff, $data);
    }

    public function delete(Staff $staff): void
    {
        if ($staff->signature) {
            Storage::disk('public')->delete($staff->signature);
        }

        $this->staffRepository->delete($staff);
    }
}
