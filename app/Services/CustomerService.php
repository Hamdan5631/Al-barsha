<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(private readonly CustomerRepository $customerRepository) {}

    public function list(int $perPage = 10): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage);
    }

    public function create(array $data): Customer
    {
        return $this->customerRepository->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        return $this->customerRepository->update($customer, $data);
    }

    public function delete(Customer $customer): void
    {
        $this->customerRepository->delete($customer);
    }
}
