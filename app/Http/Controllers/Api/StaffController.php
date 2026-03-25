<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StaffController extends Controller
{
    public function __construct(private readonly StaffService $staffService) {}

    public function index(): AnonymousResourceCollection
    {
        return StaffResource::collection($this->staffService->list());
    }

    public function store(StoreStaffRequest $request): StaffResource
    {
        return new StaffResource($this->staffService->create($request->validated()));
    }

    public function show(Staff $staff): StaffResource
    {
        return new StaffResource($staff);
    }

    public function update(UpdateStaffRequest $request, Staff $staff): StaffResource
    {
        return new StaffResource($this->staffService->update($staff, $request->validated()));
    }

    public function destroy(Staff $staff): JsonResponse
    {
        $this->staffService->delete($staff);

        return response()->json(['message' => 'Staff deleted successfully.']);
    }
}
