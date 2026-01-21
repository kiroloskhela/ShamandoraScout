<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function index(Request $request)
    {
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