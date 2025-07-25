<?php

namespace App\Http\Controllers\Api;

use App\Enums\SendLogStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\SendLogResource;
use App\Models\Post;
use App\Models\SendLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class SendLogController extends Controller
{
    /**
     * Summary of index
     *
     * @OA\Get(
     *     tags={"SendLog"},
     *     path="/send-logs",
     *     summary="List of all send logs items",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status do envio",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string",
     *             enum={"scheduled", "sent", "failed"}
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="List of all send logs items",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/SendLog")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => [new Enum(SendLogStatusEnum::class)],
        ]);

        $query = SendLog::whereHas('post', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->with('post');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy(column: 'id', direction: 'desc')->simplePaginate(20);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Summary of postLogs
     *
     * @OA\Get(
     *     tags={"SendLog"},
     *     path="/send-logs/{post}",
     *     summary="List of all send logs items",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         description="ID do post",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="List of all send logs items",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/SendLog")
     *             )
     *         )
     *     )
     * )
     */
    public function postLogs(Post $post): JsonResponse
    {
        $logs = $post->sendLogs()->orderBy(column: 'id', direction: 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => SendLogResource::collection($logs),
        ]);
    }
}
