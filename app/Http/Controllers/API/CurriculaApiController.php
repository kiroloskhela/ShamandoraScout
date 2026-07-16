<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CurriculaApiController extends Controller
{
    /**
     * GET /api/curricula
     * Optional filters:
     *  - ?category_id=1
     *  - ?marhala_id=2
     *  - ?q=search text
     *  - ?limit=50 (default 50, max 200)
     */


    

  /**
 * @OA\Tag(
 *   name="Curricula",
 *   description="Endpoints related to curricula management"
 * )
 *
 * @OA\Get(
 *   path="/api/curricula",
 *   tags={"Curricula"},
 *   summary="List curricula",
 *   @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
 *   @OA\Parameter(name="marhala_id", in="query", required=false, @OA\Schema(type="integer")),
 *   @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string")),
 *   @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=50, maximum=200)),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *   path="/api/curricula/{id}",
 *   tags={"Curricula"},
 *   summary="Get curriculum by id",
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK"),
 *   @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Get(
 *   path="/api/curricula/{id}/download",
 *   tags={"Curricula"},
 *   summary="Download curriculum file",
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="File download"),
 *   @OA\Response(response=404, description="File not found")
 * )
 *
 * @OA\Get(
 *   path="/api/curricula/meta",
 *   tags={"Curricula"},
 *   summary="Get curricula metadata",
 *   @OA\Response(response=200, description="OK")
 * )
 */







    public function index(Request $request)
    {
        Gate::authorize('curricula.view');

        $limit = (int) ($request->query('limit', 50));
        $limit = max(1, min($limit, 200));

        $q          = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $marhalaId  = $request->query('marhala_id');

        $query = DB::table('Curricula as c')
            ->join('CurriculaCategory as cc', 'c.CurriculaCategoryID', '=', 'cc.CurriculaCategoryID')
            ->join('Marhala as m', 'c.MarhalaID', '=', 'm.MarhalaID')
            ->select([
                'c.CurriculaID',
                'c.CurriculaName',
                'c.CurriculaPath',
                'c.CurriculaCategoryID',
                'cc.CurriculaCategoryName', // change if needed
                'c.MarhalaID',
                'm.MarhalaName',             // change if needed
                'c.created_at',
                'c.updated_at',
            ])
            ->orderByDesc('c.created_at');

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('c.CurriculaCategoryID', (int) $categoryId);
        }

        if ($marhalaId !== null && $marhalaId !== '') {
            $query->where('c.MarhalaID', (int) $marhalaId);
        }

        if ($q !== '') {
            $query->where('c.CurriculaName', 'like', "%{$q}%");
        }

        $items = $query->limit($limit)->get();

        // API download endpoint
        $items = $items->map(function ($r) {
            $r->download_url = url("/api/curricula/{$r->CurriculaID}/download");
            return $r;
        });

        return response()->json([
            'ok'    => true,
            'count' => $items->count(),
            'data'  => $items,
        ]);
    }

    /**
     * GET /api/curricula/{id}
     */
    public function show(int $id)
    {
        Gate::authorize('curricula.view');

        $item = DB::table('Curricula as c')
            ->join('CurriculaCategory as cc', 'c.CurriculaCategoryID', '=', 'cc.CurriculaCategoryID')
            ->join('Marhala as m', 'c.MarhalaID', '=', 'm.MarhalaID')
            ->select([
                'c.CurriculaID',
                'c.CurriculaName',
                'c.CurriculaPath',
                'c.CurriculaCategoryID',
                'cc.CurriculaCategoryName', // change if needed
                'c.MarhalaID',
                'm.MarhalaName',             // change if needed
                'c.created_at',
                'c.updated_at',
            ])
            ->where('c.CurriculaID', $id)
            ->first();

        if (!$item) {
            return response()->json(['ok' => false, 'message' => 'Curriculum not found'], 404);
        }

        $item->download_url = url("/api/curricula/{$item->CurriculaID}/download");

        return response()->json(['ok' => true, 'data' => $item]);
    }

    /**
     * GET /api/curricula/{id}/download
     * Downloads the stored curriculum file directly from API.
     */
    public function download(int $id)
    {
        Gate::authorize('curricula.view');

        $curriculum = DB::table('Curricula')->where('CurriculaID', $id)->first();

        if (!$curriculum || empty($curriculum->CurriculaPath)) {
            return response()->json(['ok' => false, 'message' => 'File not found'], 404);
        }

        // stored path example: CurriculaDocuments/xxx.pdf
        if (!Storage::exists($curriculum->CurriculaPath)) {
            return response()->json(['ok' => false, 'message' => 'File missing on disk'], 404);
        }

        // Download with original filename
        $downloadName = basename($curriculum->CurriculaPath);

        return Storage::download($curriculum->CurriculaPath, $downloadName);
    }

    /**
     * GET /api/curricula/meta
     */
    public function meta()
    {
        Gate::authorize('curricula.view');

        $categories = DB::table('CurriculaCategory')
            ->select('CurriculaCategoryID', 'CurriculaCategoryName') // change if needed
            ->orderBy('CurriculaCategoryName')
            ->get();

        $marhalat = DB::table('Marhala')
            ->select('MarhalaID', 'MarhalaName') // change if needed
            ->orderBy('MarhalaName')
            ->get();

        return response()->json([
            'ok'         => true,
            'categories' => $categories,
            'marhalat'   => $marhalat,
        ]);
    }


    
}


   