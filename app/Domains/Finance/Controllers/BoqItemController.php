<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Domains\Finance\Actions\CreateBoqItemAction;
use App\Domains\Finance\Actions\DeleteBoqItemAction;
use App\Domains\Finance\Actions\UpdateBoqItemAction;
use App\Domains\Finance\Models\BoqItem;
use App\Domains\Finance\Requests\StoreBoqItemRequest;
use App\Domains\Finance\Requests\UpdateBoqItemRequest;
use App\Domains\Finance\Resources\BoqItemResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class BoqItemController extends Controller
{
    public function __construct(
        private readonly CreateBoqItemAction $createBoqItemAction,
        private readonly UpdateBoqItemAction $updateBoqItemAction,
        private readonly DeleteBoqItemAction $deleteBoqItemAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $boqItems = BoqItem::with(['project', 'costCode'])
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return BoqItemResource::collection($boqItems)->response();
    }

    public function store(StoreBoqItemRequest $request): JsonResponse
    {
        $boqItem = $this->createBoqItemAction->execute(
            $request->toDTO(),
            $request->user()->company_id,
        );

        return response()->json(new BoqItemResource($boqItem), Response::HTTP_CREATED);
    }

    public function show(Request $request, BoqItem $boqItem): JsonResponse
    {
        $this->authorize('view', $boqItem);

        return response()->json(new BoqItemResource($boqItem));
    }

    public function update(UpdateBoqItemRequest $request, BoqItem $boqItem): JsonResponse
    {
        $boqItem = $this->updateBoqItemAction->execute($boqItem, $request->toDTO());

        return response()->json(new BoqItemResource($boqItem));
    }

    public function destroy(Request $request, BoqItem $boqItem): JsonResponse
    {
        $this->authorize('delete', $boqItem);

        $this->deleteBoqItemAction->execute($boqItem);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
