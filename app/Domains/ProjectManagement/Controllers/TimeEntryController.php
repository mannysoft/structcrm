<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Controllers;

use App\Domains\ProjectManagement\Actions\CreateTimeEntryAction;
use App\Domains\ProjectManagement\Actions\DeleteTimeEntryAction;
use App\Domains\ProjectManagement\Actions\UpdateTimeEntryAction;
use App\Domains\ProjectManagement\Models\TimeEntry;
use App\Domains\ProjectManagement\Requests\StoreTimeEntryRequest;
use App\Domains\ProjectManagement\Requests\UpdateTimeEntryRequest;
use App\Domains\ProjectManagement\Resources\TimeEntryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class TimeEntryController extends Controller
{
    public function __construct(
        private readonly CreateTimeEntryAction $createTimeEntryAction,
        private readonly UpdateTimeEntryAction $updateTimeEntryAction,
        private readonly DeleteTimeEntryAction $deleteTimeEntryAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $entries = TimeEntry::with(['project', 'user'])
            ->where('company_id', $request->user()->company_id)
            ->latest('clock_in_at')
            ->get();

        return TimeEntryResource::collection($entries)->response();
    }

    public function store(StoreTimeEntryRequest $request): JsonResponse
    {
        $entry = $this->createTimeEntryAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
            $request->user()->id,
        );

        return response()->json(new TimeEntryResource($entry), Response::HTTP_CREATED);
    }

    public function show(Request $request, TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('view', $timeEntry);

        return response()->json(new TimeEntryResource($timeEntry));
    }

    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry): JsonResponse
    {
        $timeEntry = $this->updateTimeEntryAction->execute($timeEntry, $request->toDTO());

        return response()->json(new TimeEntryResource($timeEntry));
    }

    public function destroy(Request $request, TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('delete', $timeEntry);

        $this->deleteTimeEntryAction->execute($timeEntry);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
