<?php

namespace App\Domain\Curriculum;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use stdClass;

class CurriculumPlanService
{
    public function find(int $planId): ?stdClass
    {
        return DB::table('CurriculumPlan')->where('PlanID', $planId)->first();
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function listForAdmin(?int $qetaaId = null): Collection
    {
        $lectureCount = DB::table('CurriculumPlanLecture')
            ->select('PlanID', DB::raw('COUNT(*) as LectureCount'))
            ->groupBy('PlanID');

        $query = DB::table('CurriculumPlan as p')
            ->join('Qetaa as q', 'p.QetaaID', '=', 'q.QetaaID')
            ->leftJoinSub($lectureCount, 'lc', function ($join) {
                $join->on('p.PlanID', '=', 'lc.PlanID');
            })
            ->select([
                'p.PlanID',
                'p.QetaaID',
                'q.QetaaName',
                'p.PlanName',
                'p.SortOrder',
                'p.IsActive',
                'p.created_at',
                'p.updated_at',
                DB::raw('COALESCE(lc.LectureCount, 0) as LectureCount'),
            ])
            ->orderBy('q.QetaaName')
            ->orderBy('p.SortOrder')
            ->orderBy('p.PlanID');

        if ($qetaaId !== null) {
            $query->where('p.QetaaID', $qetaaId);
        }

        return $query->get()->map(function ($plan) {
            $plan->ActiveLabel = ((int) $plan->IsActive === 1)
                ? __('Active')
                : __('Inactive');
            $plan->IsActiveFlag = (int) $plan->IsActive;
            $plan->LectureCount = (int) ($plan->LectureCount ?? 0);

            return $plan;
        });
    }

    public function create(int $qetaaId, string $planName, int $sortOrder = 0): int
    {
        if (! DB::table('Qetaa')->where('QetaaID', $qetaaId)->exists()) {
            throw ValidationException::withMessages([
                'qetaa_id' => [__('The selected sector is invalid.')],
            ]);
        }

        return (int) DB::table('CurriculumPlan')->insertGetId([
            'QetaaID' => $qetaaId,
            'PlanName' => $planName,
            'SortOrder' => $sortOrder,
            'IsActive' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function update(int $planId, string $planName, int $sortOrder): void
    {
        $plan = $this->find($planId);
        if (! $plan) {
            abort(404);
        }

        DB::table('CurriculumPlan')->where('PlanID', $planId)->update([
            'PlanName' => $planName,
            'SortOrder' => $sortOrder,
            'updated_at' => now(),
        ]);
    }

    public function delete(int $planId): void
    {
        DB::transaction(function () use ($planId) {
            $plan = DB::table('CurriculumPlan')
                ->where('PlanID', $planId)
                ->lockForUpdate()
                ->first();

            if (! $plan) {
                abort(404);
            }

            if ((int) $plan->IsActive === 1) {
                throw new RuntimeException(__('Deactivate the plan before deleting it.'));
            }

            $deleted = DB::table('CurriculumPlan')
                ->where('PlanID', $planId)
                ->where('IsActive', 0)
                ->delete();

            if ($deleted === 0) {
                throw new RuntimeException(__('Deactivate the plan before deleting it.'));
            }
        });
    }

    /**
     * @param  list<int>  $curriculaIds
     */
    public function syncLectures(int $planId, array $curriculaIds): void
    {
        $plan = $this->find($planId);
        if (! $plan) {
            abort(404);
        }

        $curriculaIds = array_values(array_unique(array_map('intval', $curriculaIds)));

        if ($curriculaIds !== []) {
            $existing = DB::table('Curricula')
                ->whereIn('CurriculaID', $curriculaIds)
                ->pluck('CurriculaID')
                ->map(fn ($id) => (int) $id)
                ->all();

            $missing = array_diff($curriculaIds, $existing);
            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'curricula_ids' => [__('One or more selected lectures are invalid.')],
                ]);
            }
        }

        DB::transaction(function () use ($planId, $curriculaIds) {
            DB::table('CurriculumPlanLecture')->where('PlanID', $planId)->delete();

            $rows = [];
            foreach ($curriculaIds as $index => $curriculaId) {
                $rows[] = [
                    'PlanID' => $planId,
                    'CurriculaID' => $curriculaId,
                    'SortOrder' => $index,
                ];
            }

            if ($rows !== []) {
                DB::table('CurriculumPlanLecture')->insert($rows);
            }
        });
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function lecturesForPlan(int $planId): Collection
    {
        return DB::table('CurriculumPlanLecture as cpl')
            ->join('Curricula as c', 'cpl.CurriculaID', '=', 'c.CurriculaID')
            ->leftJoin('CurriculaCategory as cc', 'c.CurriculaCategoryID', '=', 'cc.CurriculaCategoryID')
            ->leftJoin('Marhala as m', 'c.MarhalaID', '=', 'm.MarhalaID')
            ->where('cpl.PlanID', $planId)
            ->orderBy('cpl.SortOrder')
            ->orderBy('c.CurriculaID')
            ->select([
                'c.CurriculaID',
                'c.CurriculaName',
                'c.CurriculaCategoryID',
                'cc.CurriculaCategoryName',
                'c.MarhalaID',
                'm.MarhalaName',
                'cpl.SortOrder',
            ])
            ->get();
    }

    public function isCurriculaReferenced(int $curriculaId): bool
    {
        return DB::table('CurriculumPlanLecture')
            ->where('CurriculaID', $curriculaId)
            ->exists();
    }
}
