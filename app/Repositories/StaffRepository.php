<?php

namespace App\Repositories;

use App\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Staff::query()->latest()->paginate($perPage);
    }

    public function create(array $data): Staff
    {
        return Staff::query()->create($data);
    }

    public function update(Staff $staff, array $data): Staff
    {
        $staff->update($data);

        return $staff->refresh();
    }

    public function delete(Staff $staff): void
    {
        $staff->delete();
    }
}
