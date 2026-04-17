<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GamesApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function authUser()
    {
        return Auth::user();
    }

    private function hasAnyRole(array $roles): bool
    {
        $user = $this->authUser();

        if (!$user) {
            return false;
        }

        return $user->role()->whereIn('RoleName', $roles)->exists();
    }

    private function denyIfUnauthorized()
    {
        if (!$this->authUser()) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return null;
    }

    private function denyIfNoGamesAccess()
    {
        if ($deny = $this->denyIfUnauthorized()) {
            return $deny;
        }

        // change roles here however you want
        if (!$this->hasAnyRole(['SuperAdmin', 'AdminQetaa'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        return null;
    }

    private function findGame(int $id)
    {
        return DB::table('Games')
            ->where('GameID', $id)
            ->first();
    }

    private function nextGameId(): int
    {
        $lastGame = DB::table('Games')
            ->orderByDesc('GameID')
            ->first();

        return $lastGame ? ((int) $lastGame->GameID + 1) : 1;
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/games
     */
    public function index(Request $request)
    {
        if ($deny = $this->denyIfNoGamesAccess()) {
            return $deny;
        }

        $search = trim((string) $request->query('search', ''));

        $query = DB::table('Games');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")
                  ->orWhere('GameDescription', 'like', "%{$search}%")
                  ->orWhere('Rules', 'like', "%{$search}%")
                  ->orWhere('PointSystem', 'like', "%{$search}%")
                  ->orWhere('AgeGroup', 'like', "%{$search}%")
                  ->orWhere('Target', 'like', "%{$search}%")
                  ->orWhere('ReferenceLink', 'like', "%{$search}%")
                  ->orWhereRaw('CAST(GameID AS CHAR) LIKE ?', ["%{$search}%"]);
            });
        }

        $games = $query
            ->orderBy('GameID', 'desc')
            ->get();

        return response()->json([
            'ok' => true,
            'games' => $games,
        ]);
    }

    /**
     * GET /api/games/{id}
     */
    public function show($id)
    {
        if ($deny = $this->denyIfNoGamesAccess()) {
            return $deny;
        }

        $game = $this->findGame((int) $id);

        if (!$game) {
            return response()->json([
                'ok' => false,
                'message' => 'Game not found',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'game' => $game,
        ]);
    }

    /**
     * POST /api/games
     */
    public function store(Request $request)
    {
        if ($deny = $this->denyIfNoGamesAccess()) {
            return $deny;
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'point_system' => 'nullable|string|max:255',
            'age_group' => 'nullable|string|max:255',
            'target' => 'nullable|string|max:255',
            'require_custody' => 'nullable',
            'reference_link' => 'nullable|string|max:1000',
        ]);

        $gameId = $this->nextGameId();

        DB::table('Games')->insert([
            'GameID' => $gameId,
            'Title' => $data['title'],
            'GameDescription' => $data['description'] ?? null,
            'Rules' => $data['rules'] ?? null,
            'PointSystem' => $data['point_system'] ?? null,
            'AgeGroup' => $data['age_group'] ?? null,
            'Target' => $data['target'] ?? null,
            'RequireCustody' => $data['require_custody'] ?? null,
            'ReferenceLink' => $data['reference_link'] ?? null,
        ]);

        $game = $this->findGame($gameId);

        return response()->json([
            'ok' => true,
            'message' => 'Game created successfully',
            'game' => $game,
        ], 201);
    }

    /**
     * PUT /api/games/{id}
     */
    public function update(Request $request, $id)
    {
        if ($deny = $this->denyIfNoGamesAccess()) {
            return $deny;
        }

        $game = $this->findGame((int) $id);

        if (!$game) {
            return response()->json([
                'ok' => false,
                'message' => 'Game not found',
            ], 404);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'point_system' => 'nullable|string|max:255',
            'age_group' => 'nullable|string|max:255',
            'target' => 'nullable|string|max:255',
            'require_custody' => 'nullable',
            'reference_link' => 'nullable|string|max:1000',
        ]);

        DB::table('Games')
            ->where('GameID', (int) $id)
            ->update([
                'Title' => $data['title'],
                'GameDescription' => $data['description'] ?? null,
                'Rules' => $data['rules'] ?? null,
                'PointSystem' => $data['point_system'] ?? null,
                'AgeGroup' => $data['age_group'] ?? null,
                'Target' => $data['target'] ?? null,
                'RequireCustody' => $data['require_custody'] ?? null,
                'ReferenceLink' => $data['reference_link'] ?? null,
            ]);

        $updatedGame = $this->findGame((int) $id);

        return response()->json([
            'ok' => true,
            'message' => 'Game updated successfully',
            'game' => $updatedGame,
        ]);
    }

    /**
     * DELETE /api/games/{id}
     */
    public function destroy($id)
    {
        if ($deny = $this->denyIfNoGamesAccess()) {
            return $deny;
        }

        $game = $this->findGame((int) $id);

        if (!$game) {
            return response()->json([
                'ok' => false,
                'message' => 'Game not found',
            ], 404);
        }

        DB::table('Games')
            ->where('GameID', (int) $id)
            ->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Game deleted successfully',
        ]);
    }
}