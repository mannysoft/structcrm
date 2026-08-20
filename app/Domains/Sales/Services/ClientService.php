<?php

declare(strict_types=1);

namespace App\Domains\Sales\Services;

use App\Domains\Sales\DTOs\ClientData;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Repositories\ClientRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClientService
{
    public function __construct(
        private readonly ClientRepository $clients
    ) {}

    /**
     * @return Collection<int, Client>
     */
    public function list(int $companyId): Collection
    {
        return $this->clients->allForCompany($companyId);
    }

    /**
     * @return LengthAwarePaginator<int, Client>
     */
    public function paginate(int $companyId, string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->clients->paginateForCompany($companyId, $search, $perPage);
    }

    public function find(string $uuid, int $companyId): ?Client
    {
        return $this->clients->findForCompany($uuid, $companyId);
    }

    public function create(ClientData $data, int $companyId): Client
    {
        return $this->clients->create([
            'company_id' => $companyId,
            'name' => $data->name,
            'company_name' => $data->companyName,
            'email' => $data->email,
            'phone' => $data->phone,
            'address' => $data->address,
            'notes' => $data->notes,
        ]);
    }

    public function update(Client $client, ClientData $data): Client
    {
        return $this->clients->update($client, [
            'name' => $data->name,
            'company_name' => $data->companyName,
            'email' => $data->email,
            'phone' => $data->phone,
            'address' => $data->address,
            'notes' => $data->notes,
        ]);
    }

    public function delete(Client $client): void
    {
        $this->clients->delete($client);
    }
}
