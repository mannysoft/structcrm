<?php

declare(strict_types=1);

namespace App\Domains\Sales\Controllers;

use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Requests\StoreClientRequest;
use App\Domains\Sales\Requests\UpdateClientRequest;
use App\Domains\Sales\Resources\ClientResource;
use App\Domains\Sales\Services\ClientService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $clients = $this->clientService->list($request->user()->company_id);

        return response()->json(ClientResource::collection($clients));
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->create(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new ClientResource($client), Response::HTTP_CREATED);
    }

    public function show(Request $request, Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return response()->json(new ClientResource($client));
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client = $this->clientService->update($client, $request->toDTO());

        return response()->json(new ClientResource($client));
    }

    public function destroy(Request $request, Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $this->clientService->delete($client);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
