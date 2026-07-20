<?php

namespace App\Http\Controllers;

use App\Domain\Season\ActiveSeason;
use App\Domain\Season\SeasonPersonRollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class SeasonPersonRollController extends Controller
{
    public function __construct(
        private readonly SeasonPersonRollService $rollService,
        private readonly ActiveSeason $activeSeason,
    ) {}

    public function preview()
    {
        $season = $this->activeSeason->get();
        $seasonId = $season?->SeasonID ? (int) $season->SeasonID : null;
        $preview = $this->rollService->preview($seasonId);
        $openBatch = $seasonId ? $this->rollService->openAppliedBatchForSeason($seasonId) : null;

        return view('season.person-roll.preview', [
            'season' => $season,
            'rows' => $preview['rows'],
            'summary' => $preview['summary'],
            'blockedReason' => $preview['blocked_reason'],
            'openBatch' => $openBatch,
        ]);
    }

    public function apply(Request $request)
    {
        $season = $this->activeSeason->get();
        if (! $season) {
            return redirect()
                ->route('season-person-roll.preview')
                ->with('error', __('No active season selected.'));
        }

        $request->validate([
            'confirm' => 'accepted',
        ]);

        try {
            $result = $this->rollService->apply(
                (int) $season->SeasonID,
                Auth::id() ? (int) Auth::id() : null,
            );
        } catch (RuntimeException $e) {
            return redirect()
                ->route('season-person-roll.preview')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('season-person-roll.history')
            ->with('success', __('Season person roll applied for :count persons.', [
                'count' => $result['summary']['persons'],
            ]));
    }

    public function history()
    {
        $season = $this->activeSeason->get();

        return view('season.person-roll.history', [
            'season' => $season,
            'batches' => $this->rollService->history(),
        ]);
    }

    public function rollback(int $batchId)
    {
        try {
            $this->rollService->rollback($batchId);
        } catch (RuntimeException|\InvalidArgumentException $e) {
            return redirect()
                ->route('season-person-roll.history')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('season-person-roll.history')
            ->with('success', __('Season person roll rolled back successfully.'));
    }
}
