<?php

namespace App\Http\Controllers\API;

use App\Domain\Curriculum\ActiveCurriculumPlan;
use App\Domain\Curriculum\CurriculumPlanService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CurriculumPlanApiController extends Controller
{
    /**
     * @OA\Tag(
     *   name="CurriculumPlans",
     *   description="Active curriculum plans (منهج) per sector"
     * )
     *
     * @OA\Get(
     *   path="/api/curriculum-plans/active",
     *   tags={"CurriculumPlans"},
     *   summary="List all active curriculum plans",
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * @OA\Get(
     *   path="/api/curriculum-plans/active/{qetaaId}",
     *   tags={"CurriculumPlans"},
     *   summary="Get the active curriculum plan for a sector",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(name="qetaaId", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=404, description="Sector not found")
     * )
     */
    public function activeAll(ActiveCurriculumPlan $active, CurriculumPlanService $plans)
    {
        Gate::authorize('curriculumPlan.view');

        $items = $active->allActive()->map(function ($plan) use ($plans) {
            return $this->serializePlan($plan, $plans);
        })->values();

        return response()->json([
            'ok' => true,
            'count' => $items->count(),
            'data' => $items,
        ]);
    }

    public function activeForQetaa(int $qetaaId, ActiveCurriculumPlan $active, CurriculumPlanService $plans)
    {
        Gate::authorize('curriculumPlan.view');

        $qetaaExists = DB::table('Qetaa')->where('QetaaID', $qetaaId)->exists();
        if (! $qetaaExists) {
            return response()->json(['ok' => false, 'message' => 'Sector not found'], 404);
        }

        $plan = $active->forQetaa($qetaaId);
        if (! $plan) {
            return response()->json([
                'ok' => true,
                'data' => null,
            ]);
        }

        return response()->json([
            'ok' => true,
            'data' => $this->serializePlan($plan, $plans),
        ]);
    }

    private function serializePlan(object $plan, CurriculumPlanService $plans): array
    {
        $lectures = $plans->lecturesForPlan((int) $plan->PlanID)->map(function ($lecture) {
            return [
                'CurriculaID' => (int) $lecture->CurriculaID,
                'CurriculaName' => $lecture->CurriculaName,
                'CurriculaCategoryID' => $lecture->CurriculaCategoryID !== null ? (int) $lecture->CurriculaCategoryID : null,
                'CurriculaCategoryName' => $lecture->CurriculaCategoryName,
                'MarhalaID' => $lecture->MarhalaID !== null ? (int) $lecture->MarhalaID : null,
                'MarhalaName' => $lecture->MarhalaName,
                'SortOrder' => (int) $lecture->SortOrder,
                'download_url' => url('/api/curricula/'.$lecture->CurriculaID.'/download'),
            ];
        })->values()->all();

        return [
            'PlanID' => (int) $plan->PlanID,
            'QetaaID' => (int) $plan->QetaaID,
            'PlanName' => $plan->PlanName,
            'SortOrder' => (int) $plan->SortOrder,
            'lectures' => $lectures,
        ];
    }
}
