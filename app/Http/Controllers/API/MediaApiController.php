<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MediaApiController extends Controller
{
    /**
     * GET /api/media/seasons
     * Returns seasons list for dropdown.
     */



    /**
 * @OA\Tag(
 *   name="Media",
 *   description="Media endpoints (seasons, events, and drive links)"
 * )
 *
 * @OA\Get(
 *   path="/api/media/seasons",
 *   operationId="mediaSeasons",
 *   tags={"Media"},
 *   summary="Get seasons list",
 *   description="Returns seasons list for dropdown.",
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(
 *         property="seasons",
 *         type="array",
 *         @OA\Items(
 *           type="object",
 *           @OA\Property(property="SeasonID", type="integer", example=1),
 *           @OA\Property(property="SeasonName", type="string", example="Season A"),
 *           @OA\Property(property="SeasonYear", type="integer", example=2025)
 *         )
 *       )
 *     )
 *   )
 * )
 *
 * @OA\Get(
 *   path="/api/media/events",
 *   operationId="mediaEvents",
 *   tags={"Media"},
 *   summary="Get events by season",
 *   description="Returns events for a selected season.",
 *   @OA\Parameter(
 *     name="season_id",
 *     in="query",
 *     required=true,
 *     description="SeasonID",
 *     @OA\Schema(type="integer", example=1)
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(
 *         property="events",
 *         type="array",
 *         @OA\Items(
 *           type="object",
 *           @OA\Property(property="SeasonEventID", type="integer", example=999),
 *           @OA\Property(property="SeasonID", type="integer", example=1),
 *           @OA\Property(property="EventID", type="integer", example=10),
 *           @OA\Property(property="EventName", type="string", example="Camp Day"),
 *           @OA\Property(property="EventStartDate", type="string", format="date-time", example="2025-09-01 10:00:00"),
 *           @OA\Property(property="EventEndDate", type="string", format="date-time", example="2025-09-01 18:00:00")
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=422,
 *     description="Validation error",
 *     @OA\JsonContent(type="object")
 *   )
 * )
 *
 * @OA\Get(
 *   path="/api/media/links",
 *   operationId="mediaLinksLegacy",
 *   tags={"Media"},
 *   summary="Get drive links by SeasonEventID (query param)",
 *   description="Returns drive links for selected SeasonEventID. Also returns preview_link for embedding.",
 *   @OA\Parameter(
 *     name="season_event_id",
 *     in="query",
 *     required=true,
 *     description="SeasonEventID",
 *     @OA\Schema(type="integer", example=999)
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(property="count", type="integer", example=2),
 *       @OA\Property(
 *         property="links",
 *         type="array",
 *         @OA\Items(
 *           type="object",
 *           @OA\Property(property="MediaID", type="integer", example=50),
 *           @OA\Property(property="SeasonEventID", type="integer", example=999),
 *           @OA\Property(property="DriveLink", type="string", example="https://drive.google.com/file/d/FILE_ID/view?usp=sharing"),
 *           @OA\Property(property="preview_link", type="string", example="https://drive.google.com/file/d/FILE_ID/preview")
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=422,
 *     description="Validation error",
 *     @OA\JsonContent(type="object")
 *   )
 * )
 *
 * @OA\Get(
 *   path="/api/media/links/{seasonEventId}",
 *   operationId="mediaLinks",
 *   tags={"Media"},
 *   summary="Get drive links by SeasonEventID (path param)",
 *   description="Same as /api/media/links but uses a path parameter.",
 *   @OA\Parameter(
 *     name="seasonEventId",
 *     in="path",
 *     required=true,
 *     description="SeasonEventID",
 *     @OA\Schema(type="integer", example=999)
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(property="count", type="integer", example=2),
 *       @OA\Property(
 *         property="links",
 *         type="array",
 *         @OA\Items(
 *           type="object",
 *           @OA\Property(property="MediaID", type="integer", example=50),
 *           @OA\Property(property="SeasonEventID", type="integer", example=999),
 *           @OA\Property(property="DriveLink", type="string", example="https://drive.google.com/file/d/FILE_ID/view?usp=sharing"),
 *           @OA\Property(property="preview_link", type="string", example="https://drive.google.com/file/d/FILE_ID/preview")
 *         )
 *       )
 *     )
 *   )
 * )
 */

    public function seasons()
    {
        $seasons = DB::table('Season')
            ->select('SeasonID', 'SeasonName', 'SeasonYear')
            ->orderBy('SeasonYear', 'desc')
            ->get();

        return response()->json(['ok' => true, 'seasons' => $seasons]);
    }

    /**
     * GET /api/media/events?season_id=1
     * Returns events for a selected season.
     */
    public function events(Request $request)
    {
        $request->validate([
            'season_id' => 'required|integer'
        ]);

        $seasonId = (int) $request->query('season_id');

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->where('se.SeasonID', $seasonId)
            ->select(
                'se.SeasonEventID',
                'se.SeasonID',
                'e.EventID',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate'
            )
            ->orderBy('e.EventStartDate', 'asc')
            ->get();

        return response()->json(['ok' => true, 'events' => $events]);
    }

    /**
     * GET /api/media/links?season_event_id=999
     * Returns drive links for selected SeasonEventID.
     * Also returns a "preview_link" that is safer for embedding.
     */
    public function links(Request $request)
    {
        $request->validate([
            'season_event_id' => 'required|integer'
        ]);

        $seasonEventId = (int) $request->query('season_event_id');

        $media = DB::table('Media')
            ->where('SeasonEventID', $seasonEventId)
            ->select('MediaID', 'SeasonEventID', 'DriveLink')
            ->orderByDesc('MediaID')
            ->get();

        $media = $media->map(function ($m) {
            $m->preview_link = $this->toDrivePreviewLink($m->DriveLink);
            return $m;
        });

        return response()->json([
            'ok' => true,
            'count' => $media->count(),
            'links' => $media
        ]);
    }

    /**
     * GET /api/media/links/{seasonEventId}
     * Same as links() but via path param.
     */
    public function linksBySeasonEventId(int $seasonEventId)
    {
        $media = DB::table('Media')
            ->where('SeasonEventID', $seasonEventId)
            ->select('MediaID', 'SeasonEventID', 'DriveLink')
            ->orderByDesc('MediaID')
            ->get();

        $media = $media->map(function ($m) {
            $m->preview_link = $this->toDrivePreviewLink($m->DriveLink);
            return $m;
        });

        return response()->json([
            'ok' => true,
            'count' => $media->count(),
            'links' => $media
        ]);
    }

    // ---------------- helper ----------------

    /**
     * Convert common Google Drive links into an embeddable preview link.
     * Supports:
     *  - https://drive.google.com/file/d/{id}/view?...
     *  - https://drive.google.com/open?id={id}
     *  - https://drive.google.com/uc?id={id}&export=download
     *
     * Returns original link if it can't detect an id.
     */
    private function toDrivePreviewLink(string $url): string
    {






        $fileId = null;

    
        if (preg_match('~drive\.google\.com/file/d/([^/]+)~', $url, $m)) {
            $fileId = $m[1];
        }

        // ?id={id}
        if (!$fileId) {
            $parts = parse_url($url);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $qs);
                if (!empty($qs['id'])) {
                    $fileId = $qs['id'];
                }
            }
        }

        if ($fileId) {
            return "https://drive.google.com/file/d/{$fileId}/preview";
        }

        return $url;
    }


    
}