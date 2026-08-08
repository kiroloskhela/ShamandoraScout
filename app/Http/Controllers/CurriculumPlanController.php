<?php

namespace App\Http\Controllers;

use App\Domain\Curriculum\ActiveCurriculumPlan;
use App\Domain\Curriculum\CurriculumPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class CurriculumPlanController extends Controller
{
    public function index(Request $request, CurriculumPlanService $plans)
    {
        $qetaaId = $request->query('qetaa_id');
        $filterQetaaId = ($qetaaId !== null && $qetaaId !== '') ? (int) $qetaaId : null;

        $rows = $plans->listForAdmin($filterQetaaId);
        $qetaat = DB::table('Qetaa')->orderBy('QetaaName')->get();

        return view('curriculum_plans.index', [
            'plans' => $rows,
            'qetaat' => $qetaat,
            'filterQetaaId' => $filterQetaaId,
        ]);
    }

    public function create()
    {
        $qetaat = DB::table('Qetaa')->orderBy('QetaaName')->get();

        return view('curriculum_plans.create', compact('qetaat'));
    }

    public function insert(Request $request, CurriculumPlanService $plans)
    {
        $validator = Validator::make($request->all(), [
            'qetaa_id' => 'required|integer|exists:Qetaa,QetaaID',
            'plan_name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $plans->create(
            (int) $request->input('qetaa_id'),
            (string) $request->input('plan_name'),
            (int) ($request->input('sort_order') ?? 0),
        );

        return redirect()
            ->route('curriculum-plan.index')
            ->with('success', __('Curriculum plan created successfully.'));
    }

    public function edit(int $id, CurriculumPlanService $plans)
    {
        $plan = $plans->find($id);
        if (! $plan) {
            abort(404);
        }

        $qetaa = DB::table('Qetaa')->where('QetaaID', $plan->QetaaID)->first();
        $lectures = $plans->lecturesForPlan($id);
        $selectedIds = $lectures->pluck('CurriculaID')->map(fn ($v) => (int) $v)->all();
        $curricula = DB::table('Curricula as c')
            ->leftJoin('CurriculaCategory as cc', 'c.CurriculaCategoryID', '=', 'cc.CurriculaCategoryID')
            ->leftJoin('Marhala as m', 'c.MarhalaID', '=', 'm.MarhalaID')
            ->orderBy('c.CurriculaName')
            ->select([
                'c.CurriculaID',
                'c.CurriculaName',
                'cc.CurriculaCategoryName',
                'm.MarhalaName',
            ])
            ->get();

        return view('curriculum_plans.edit', [
            'plan' => $plan,
            'qetaa' => $qetaa,
            'curricula' => $curricula,
            'selectedIds' => $selectedIds,
        ]);
    }

    public function update(Request $request, int $id, CurriculumPlanService $plans)
    {
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'curricula_ids' => 'nullable|array',
            'curricula_ids.*' => 'integer|exists:Curricula,CurriculaID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($plans, $id, $request) {
            $plans->update(
                $id,
                (string) $request->input('plan_name'),
                (int) ($request->input('sort_order') ?? 0),
            );
            $plans->syncLectures($id, $request->input('curricula_ids', []));
        });

        return redirect()
            ->route('curriculum-plan.edit', $id)
            ->with('success', __('Curriculum plan updated successfully.'));
    }

    public function delete(int $id, CurriculumPlanService $plans)
    {
        $plan = $plans->find($id);
        if (! $plan) {
            abort(404);
        }

        $qetaa = DB::table('Qetaa')->where('QetaaID', $plan->QetaaID)->first();

        return view('curriculum_plans.delete', compact('plan', 'qetaa'));
    }

    public function destroy(int $id, CurriculumPlanService $plans)
    {
        try {
            $plans->delete($id);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('curriculum-plan.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('curriculum-plan.index')
            ->with('success', __('Curriculum plan deleted successfully.'));
    }

    public function activate(int $id, ActiveCurriculumPlan $active)
    {
        $active->activate($id);

        return redirect()
            ->route('curriculum-plan.index')
            ->with('success', __('Curriculum plan activated. Other plans in this sector were deactivated.'));
    }

    public function deactivate(int $id, ActiveCurriculumPlan $active)
    {
        $active->deactivate($id);

        return redirect()
            ->route('curriculum-plan.index')
            ->with('success', __('Curriculum plan deactivated.'));
    }
}
