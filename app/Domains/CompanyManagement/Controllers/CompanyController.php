<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Controllers;

use App\Domains\CompanyManagement\Actions\CreateCompanyAction;
use App\Domains\CompanyManagement\Actions\DeleteCompanyAction;
use App\Domains\CompanyManagement\Actions\UpdateCompanyAction;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\CompanyManagement\Requests\StoreCompanyRequest;
use App\Domains\CompanyManagement\Requests\UpdateCompanyRequest;
use App\Domains\CompanyManagement\Resources\CompanyResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class CompanyController extends Controller
{
    public function __construct(
        private readonly CreateCompanyAction $createCompanyAction,
        private readonly UpdateCompanyAction $updateCompanyAction,
        private readonly DeleteCompanyAction $deleteCompanyAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $company = $request->user()?->company;

        return response()->json($company ? new CompanyResource($company) : null);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->createCompanyAction->execute($request->toDTO());

        return response()->json(new CompanyResource($company), Response::HTTP_CREATED);
    }

    public function show(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return response()->json(new CompanyResource($company));
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $company = $this->updateCompanyAction->execute($company, $request->toDTO());

        return response()->json(new CompanyResource($company));
    }

    public function destroy(Request $request, Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $this->deleteCompanyAction->execute($company);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
