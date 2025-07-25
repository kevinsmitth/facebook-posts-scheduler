<?php

namespace App\Http\Controllers\Api;

use App\Actions\Facebook\DestroyFacebookAction;
use App\Actions\Facebook\StoreFacebookAction;
use App\Enums\PostStatusEnum;
use App\Enums\SendLogStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\SendLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

/**
 * @OA\OpenApi(
 *
 *   @OA\Info(
 *       title="Returns Services API",
 *       version="1.0.0",
 *       description="API documentation for Todo items",
 *
 *       @OA\Contact(
 *           email="test@example.com"
 *       ),
 *   ),
 *
 *   @OA\Server(
 *       description="Returns Services API",
 *       url=L5_SWAGGER_CONST_HOST
 *   ),
 *
 *   @OA\PathItem(
 *       path="/"
 *   )
 *  ),
 */
class PostController extends Controller
{
    /**
     * Summary of index
     *
     * @OA\Get(
     *     tags={"Posts"},
     *     path="/posts",
     *     summary="List of all posts items",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status do post",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string",
     *             enum={"scheduled", "sent", "failed"}
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Busca por título ou conteúdo",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Data inicial",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Data final",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Retorna todos os posts",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['nullable', new Enum(PostStatusEnum::class)],
                'search' => ['nullable', 'string', 'max:255'],
                'date_from' => ['nullable', 'date'],
                'date_to' => ['nullable', 'date'],
            ]);

            $query = Auth::user()->posts();

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            if ($request->has('date_from')) {
                $query->whereDate('scheduled_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('scheduled_at', '<=', $request->date_to);
            }

            $postTotal = $query->count();
            $posts = $query->orderBy(column: 'id', direction: 'desc')->simplePaginate(10);

            return response()->json([
                'success' => true,
                'data' => PostResource::collection($posts->items()),
                'has_more_pages' => $posts->hasMorePages(),
                'per_page' => $posts->perPage(),
                'total' => $postTotal,
                'current_page' => $posts->currentPage(),
                'first_page_url' => $posts->url(1),
                'next_page_url' => $posts->nextPageUrl(),
                'previous_page_url' => $posts->previousPageUrl(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    /**
     * Summary of store
     *
     * @OA\Post(
     *     tags={"Posts"},
     *     path="/posts",
     *     summary="Create a new post",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 required={"title", "content"},
     *
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     example="Título do post"
     *                 ),
     *                 @OA\Property(
     *                     property="content",
     *                     type="string",
     *                     example="Conteúdo do post"
     *                 ),
     *                 @OA\Property(
     *                     property="scheduled_for",
     *                     type="datetime",
     *                     example=""
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                     example=""
     *                 ),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Post criado com sucesso",
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
     *                 type="object",
     *                 ref="#/components/schemas/Post"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Post criado com sucesso"
     *             )
     *         )
     *     )
     * )
     */
    public function store(StorePostRequest $request, StoreFacebookAction $storeFacebookAction): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::user()->id;

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('posts', 'public');
            }

            if ($request->has('scheduled_for') && $request->filled('scheduled_for')) {
                $data['scheduled_for'] = Carbon::parse($data['scheduled_for'])->format('Y-m-d H:i:s');
                $data['scheduled_at'] = now();
                $data['status'] = PostStatusEnum::SCHEDULED;

                $post = Post::create($data);

                return response()->json([
                    'success' => true,
                    'data' => PostResource::make($post),
                    'message' => 'Post agendado com sucesso.',
                ], 201);
            }

            $result = $storeFacebookAction->execute(
                $request->title,
                $request->content,
                $data['image_path'] ?? null
            );

            if ($result['success']) {
                $data['social_media_response'] = json_encode($result['data']);
                $data['status'] = PostStatusEnum::SENT;
                $data['sent_at'] = now();
            } else {
                $data['social_media_response'] = json_encode($result['error']);
                $data['status'] = PostStatusEnum::FAILED;
                $data['failed_at'] = now();
            }

            unset($data['image']);

            $post = Post::create($data);

            SendLog::create([
                'post_id' => $post->id,
                'user_id' => Auth::user()->id,
                'status' => SendLogStatusEnum::SENT,
                'response' => $data['social_media_response'],
            ]);

            return response()->json([
                'success' => true,
                'data' => PostResource::make($post),
                'message' => $result['success'] ? 'Post criado e publicado com sucesso.' : 'Post foi criado, mas não foi publicado.',
            ], 201);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    /**
     * Summary of show
     *
     * @OA\Get(
     *     tags={"Posts"},
     *     path="/posts/{post}",
     *     summary="Retorna um post",
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
     *         response=200,
     *         description="Retorna um post",
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
     *                 type="object",
     *                 ref="#/components/schemas/Post"
     *             )
     *         )
     *     )
     * )
     */
    public function show(Post $post): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => PostResource::make($post),
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    /**
     * Summary of destroy
     *
     * @OA\Delete(
     *     tags={"Posts"},
     *     path="/posts/{post}",
     *     summary="Deleta um post",
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
     *         response=200,
     *         description="Post deletado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *         )
     *     )
     * )
     */
    public function destroy(Post $post, DestroyFacebookAction $destroyFacebookAction): JsonResponse
    {
        try {
            $socialMediaPostId = data_get(json_decode($post->social_media_response, true), 'post_id') ??
                data_get(json_decode($post->social_media_response, true), 'id');

            if (! $socialMediaPostId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post não encontrado',
                    'oi' => $socialMediaPostId,
                ]);
            }

            $destroyFacebookAction->execute(postId: $socialMediaPostId);

            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }

            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deletado com sucesso',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Post não encontrado',
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar post',
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/posts/{post}/retry",
     *     tags={"Posts"},
     *     summary="Reenvia um post",
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
     *         response=200,
     *         description="Post reenviado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *         )
     *     )
     * )
     *  */
    public function retry(Post $post, StoreFacebookAction $action): JsonResponse
    {
        try {
            if ($post->status !== PostStatusEnum::FAILED) {
                return response()->json(['success' => false, 'error' => 'Post não está com status failed']);
            }

            $post->update(['status' => PostStatusEnum::SENT]);

            $result = $action->execute($post->title, $post->content, $post->image_path);

            $post->update([
                'status' => $result['success'] ? PostStatusEnum::SENT : PostStatusEnum::FAILED,
                'social_media_response' => json_encode($result['data']),
                'sent_at' => $result['success'] ? now() : null,
                'failed_at' => $result['success'] ? null : now(),
            ]);

            return response()->json(array_merge($result, ['post' => $post]));
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json(['success' => false, 'error' => 'Erro ao reenviar post']);
        }
    }
}
