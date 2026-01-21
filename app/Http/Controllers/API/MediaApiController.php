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

        // /file/d/{id}/...
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
