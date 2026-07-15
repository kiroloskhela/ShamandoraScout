<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GamesApiController extends Controller
{
    /**
     * GET /api/games
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Game::class);

        $search = trim((string) $request->query('search', ''));

        $query = Game::query();

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

        $games = $query->orderByDesc('GameID')->get();

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
        $game = Game::query()->find((int) $id);

        if (!$game) {
            return response()->json([
                'ok' => false,
                'message' => 'Game not found',
            ], 404);
        }

        $this->authorize('view', $game);

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
        $this->authorize('create', Game::class);

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

        $game = Game::query()->create([
            'Title' => $data['title'],
            'GameDescription' => $data['description'] ?? null,
            'Rules' => $data['rules'] ?? null,
            'PointSystem' => $data['point_system'] ?? null,
            'AgeGroup' => $data['age_group'] ?? null,
            'Target' => $data['target'] ?? null,
            'RequireCustody' => $data['require_custody'] ?? null,
            'ReferenceLink' => $data['reference_link'] ?? null,
        ]);

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
        $game = Game::query()->find((int) $id);

        if (!$game) {
            return response()->json([
                'ok' => false,
                'message' => 'Game not found',
            ], 404);
        }

        $this->authorize('update', $game);

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

        $game->update([
            'Title' => $data['title'],
            'GameDescription' => $data['description'] ?? null,
            'Rules' => $data['rules'] ?? null,
            'PointSystem' => $data['point_system'] ?? null,
            'AgeGroup' => $data['age_group'] ?? null,
            'Target' => $data['target'] ?? null,
            'RequireCustody' => $data['require_custody'] ?? null,
            'ReferenceLink' => $data['reference_link'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Game updated successfully',
            'game' => $game->fresh(),
        ]);
    }

    /**
     * DELETE /api/games/{id}
     */
    public function destroy($id)
    {
        $game = Game::query()->find((int) $id);

        if (!$game) {
            return response()->json([
                'ok' => false,
                'message' => 'Game not found',
            ], 404);
        }

        $this->authorize('delete', $game);
        $game->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Game deleted successfully',
        ]);
    }
}
