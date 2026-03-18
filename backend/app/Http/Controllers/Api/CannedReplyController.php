<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CannedReply;
use App\Models\User;
use App\Support\PortalData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CannedReplyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isStaff(), 403);

        $replies = CannedReply::query()
            ->where('firm_id', $user->firm_id)
            ->orderBy('category')
            ->orderBy('title')
            ->get();

        return response()->json([
            'data' => $replies->map(fn (CannedReply $reply) => PortalData::cannedReply($reply))->all(),
        ]);
    }
}
