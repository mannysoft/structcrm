<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Domains\Finance\Actions\CreateProgressReportAction;
use App\Domains\Finance\Actions\DeleteProgressReportAction;
use App\Domains\Finance\Actions\UpdateProgressReportAction;
use App\Domains\Finance\Models\ProgressReport;
use App\Domains\Finance\Requests\StoreProgressReportRequest;
use App\Domains\Finance\Requests\UpdateProgressReportRequest;
use App\Domains\Finance\Resources\ProgressReportResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ProgressReportController extends Controller
{
    public function __construct(
        private readonly CreateProgressReportAction $createProgressReportAction,
        private readonly UpdateProgressReportAction $updateProgressReportAction,
        private readonly DeleteProgressReportAction $deleteProgressReportAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $progressReports = ProgressReport::with(['project', 'items'])
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return ProgressReportResource::collection($progressReports)->response();
    }

    public function store(StoreProgressReportRequest $request): JsonResponse
    {
        $progressReport = $this->createProgressReportAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new ProgressReportResource($progressReport), Response::HTTP_CREATED);
    }

    public function show(Request $request, ProgressReport $progressReport): JsonResponse
    {
        $this->authorize('view', $progressReport);

        return response()->json(new ProgressReportResource($progressReport->load('items')));
    }

    public function update(UpdateProgressReportRequest $request, ProgressReport $progressReport): JsonResponse
    {
        $progressReport = $this->updateProgressReportAction->execute($progressReport, $request->toDTO());

        return response()->json(new ProgressReportResource($progressReport));
    }

    public function destroy(Request $request, ProgressReport $progressReport): JsonResponse
    {
        $this->authorize('delete', $progressReport);

        $this->deleteProgressReportAction->execute($progressReport);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
