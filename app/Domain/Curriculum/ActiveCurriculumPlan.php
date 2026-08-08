<?php

namespace App\Domain\Curriculum;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class ActiveCurriculumPlan
{
    public function forQetaa(int $qetaaId): ?stdClass
    {
        return DB::table('CurriculumPlan')
            ->where('QetaaID', $qetaaId)
            ->where('IsActive', 1)
            ->orderBy('PlanID')
            ->first();
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function allActive(): Collection
    {
        return DB::table('CurriculumPlan')
            ->where('IsActive', 1)
            ->orderBy('QetaaID')
            ->orderBy('PlanID')
            ->get();
    }

    public function activate(int $planId): void
    {
        DB::transaction(function () use ($planId) {
            $plan = DB::table('CurriculumPlan')->where('PlanID', $planId)->first();
            if (! $plan) {
                abort(404);
            }

            $qetaaId = (int) $plan->QetaaID;

            // Lock every plan in this sector in a stable order to avoid deadlocks.
            $locked = DB::table('CurriculumPlan')
                ->where('QetaaID', $qetaaId)
                ->orderBy('PlanID')
                ->lockForUpdate()
                ->get();

            if (! $locked->contains(fn ($row) => (int) $row->PlanID === $planId)) {
                abort(404);
            }

            DB::table('CurriculumPlan')
                ->where('QetaaID', $qetaaId)
                ->update(['IsActive' => 0, 'updated_at' => now()]);

            DB::table('CurriculumPlan')
                ->where('PlanID', $planId)
                ->update(['IsActive' => 1, 'updated_at' => now()]);
        });
    }

    public function deactivate(int $planId): void
    {
        DB::transaction(function () use ($planId) {
            $plan = DB::table('CurriculumPlan')
                ->where('PlanID', $planId)
                ->lockForUpdate()
                ->first();

            if (! $plan) {
                abort(404);
            }

            DB::table('CurriculumPlan')
                ->where('PlanID', $planId)
                ->update(['IsActive' => 0, 'updated_at' => now()]);
        });
    }
}
