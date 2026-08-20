<?php

declare(strict_types=1);

namespace App\Domains\Sales\Repositories;

use App\Domains\Sales\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository
{
    /**
     * Get all clients belonging to the given company.
     *
     * @return Collection<int, Client>
     */
    public function allForCompany(int $companyId): Collection
    {
        return Client::where('company_id', $companyId)->latest()->get();
    }

    /**
     * Paginate clients belonging to the given company, optionally filtered by search.
     *
     * @return LengthAwarePaginator<int, Client>
     */
    public function paginateForCompany(int $companyId, string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return Client::query()
            ->where('company_id', $companyId)
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->latest()
            ->paginate($perPage);
    }

    public function findForCompany(string $uuid, int $companyId): ?Client
    {
        return Client::where('uuid', $uuid)
            ->where('company_id', $companyId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}
