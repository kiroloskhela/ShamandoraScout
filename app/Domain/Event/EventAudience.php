<?php

namespace App\Domain\Event;

use App\Domain\Enrolment\LiveFormQetaaResolver;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who an event includes.
 *
 * Normal sectors always mean everyone in that sector.
 * قادة، جوالة، and اعداد قادة can mean everyone, or only people of that
 * sector who are linked to a group inside one of the event's other sectors.
 */
class EventAudience
{
    public const ALL = 'all';

    public const SERVING = 'serving';

    /**
     * @param  list<int|string>  $hostIds
     * @param  array<int|string, string>  $scopes
     * @return list<array{QetaaID: int, AudienceScope: string}>
     */
    public function rowsFromInput(array $hostIds, array $scopes): array
    {
        $hosts = $this->hostIds($hostIds);
        $rows = [];

        foreach ($hosts as $id) {
            $rows[] = ['QetaaID' => $id, 'AudienceScope' => self::ALL];
        }

        foreach ($scopes as $id => $scope) {
            $id = (int) $id;
            if (! in_array($id, LiveFormQetaaResolver::FROZEN_QETAA_IDS, true)) {
                continue;
            }
            if ($scope !== self::ALL && $scope !== self::SERVING) {
                continue;
            }
            if ($scope === self::SERVING && $hosts === []) {
                continue;
            }
            $rows[] = ['QetaaID' => $id, 'AudienceScope' => $scope];
        }

        return $rows;
    }

    /**
     * @param  list<int|string>  $hostIds
     * @param  array<int|string, string>  $scopes
     */
    public function hasServingWithoutHost(array $hostIds, array $scopes): bool
    {
        if ($this->hostIds($hostIds) !== []) {
            return false;
        }

        foreach ($scopes as $id => $scope) {
            if (in_array((int) $id, LiveFormQetaaResolver::FROZEN_QETAA_IDS, true) && $scope === self::SERVING) {
                return true;
            }
        }

        return false;
    }

    public function personIsEligible(int $eventId, int $personId): bool
    {
        $query = DB::table('PersonQetaa as pq')->where('pq.PersonID', $personId);
        $this->wherePersonQualifies($query, $eventId, 'pq.PersonID', 'pq.QetaaID');

        return $query->exists();
    }

    public function wherePersonQualifies(Builder $query, int $eventId, string $personIdColumn, string $qetaaIdColumn): void
    {
        $query->whereExists(function ($sub) use ($eventId, $personIdColumn, $qetaaIdColumn) {
            $sub->selectRaw('1')
                ->from('EventQetaa as eq')
                ->where('eq.EventID', $eventId)
                ->whereColumn('eq.QetaaID', $qetaaIdColumn);
            $this->applyScope($sub, 'eq', $personIdColumn);
        });
    }

    public function whereJoinedLinkQualifies(Builder $query, string $eqAlias = 'eq', string $personIdColumn = 'pq.PersonID'): void
    {
        if (! $this->hasScopeColumn()) {
            return;
        }

        $query->where(function ($scope) use ($eqAlias, $personIdColumn) {
            $this->scopeMatch($scope, $eqAlias, $personIdColumn);
        });
    }

    public function whereVisibleToPerson(Builder $query, int $personId, string $eventIdColumn): void
    {
        $query->where(function ($visible) use ($personId, $eventIdColumn) {
            $visible->whereExists(function ($sub) use ($personId, $eventIdColumn) {
                $sub->selectRaw('1')
                    ->from('PersonGroup as pg_vis')
                    ->join('GroupQetaa as gq_vis', 'gq_vis.GroupID', '=', 'pg_vis.GroupID')
                    ->join('EventQetaa as eq_vis', 'eq_vis.QetaaID', '=', 'gq_vis.QetaaID')
                    ->whereColumn('eq_vis.EventID', $eventIdColumn)
                    ->where('pg_vis.PersonID', $personId);

                if ($this->hasScopeColumn()) {
                    $sub->where(function ($all) {
                        $all->where('eq_vis.AudienceScope', self::ALL)
                            ->orWhereNull('eq_vis.AudienceScope');
                    });
                }
            });

            if (! $this->hasScopeColumn()) {
                return;
            }

            $visible->orWhereExists(function ($sub) use ($personId, $eventIdColumn) {
                $sub->selectRaw('1')
                    ->from('PersonQetaa as pq_vis')
                    ->join('EventQetaa as eq_vis', 'eq_vis.QetaaID', '=', 'pq_vis.QetaaID')
                    ->whereColumn('eq_vis.EventID', $eventIdColumn)
                    ->where('pq_vis.PersonID', $personId)
                    ->whereIn('eq_vis.QetaaID', LiveFormQetaaResolver::FROZEN_QETAA_IDS)
                    ->where(function ($all) {
                        $all->where('eq_vis.AudienceScope', self::ALL)
                            ->orWhereNull('eq_vis.AudienceScope');
                    });
            });

            $visible->orWhereExists(function ($sub) use ($personId, $eventIdColumn) {
                $sub->selectRaw('1')
                    ->from('PersonQetaa as pq_vis')
                    ->join('EventQetaa as eq_vis', 'eq_vis.QetaaID', '=', 'pq_vis.QetaaID')
                    ->whereColumn('eq_vis.EventID', $eventIdColumn)
                    ->where('pq_vis.PersonID', $personId)
                    ->where('eq_vis.AudienceScope', self::SERVING)
                    ->whereExists(function ($group) use ($personId) {
                        $this->servingGroup($group, 'eq_vis.EventID', $personId);
                    });
            });
        });
    }

    /**
     * @param  list<int|string>  $hostIds
     * @return list<int>
     */
    private function hostIds(array $hostIds): array
    {
        $hosts = [];
        foreach ($hostIds as $id) {
            $id = (int) $id;
            if ($id <= 0 || in_array($id, LiveFormQetaaResolver::FROZEN_QETAA_IDS, true)) {
                continue;
            }
            $hosts[$id] = $id;
        }

        return array_values($hosts);
    }

    private function applyScope(Builder $sub, string $eqAlias, string $personIdColumn): void
    {
        if (! $this->hasScopeColumn()) {
            return;
        }

        $sub->where(function ($scope) use ($eqAlias, $personIdColumn) {
            $this->scopeMatch($scope, $eqAlias, $personIdColumn);
        });
    }

    private function scopeMatch(Builder $scope, string $eqAlias, string $personIdColumn): void
    {
        $scope->where(function ($all) use ($eqAlias) {
            $all->where($eqAlias.'.AudienceScope', self::ALL)
                ->orWhereNull($eqAlias.'.AudienceScope');
        })->orWhere(function ($serving) use ($eqAlias, $personIdColumn) {
            $serving->where($eqAlias.'.AudienceScope', self::SERVING)
                ->whereExists(function ($group) use ($eqAlias, $personIdColumn) {
                    $this->servingGroup($group, $eqAlias.'.EventID', $personIdColumn);
                });
        });
    }

    private function servingGroup(Builder $group, string $eventIdColumn, string|int $personIdColumn): void
    {
        $group->selectRaw('1')
            ->from('PersonGroup as pg_srv')
            ->join('GroupQetaa as gq_srv', 'gq_srv.GroupID', '=', 'pg_srv.GroupID')
            ->join('EventQetaa as host_eq', function ($join) use ($eventIdColumn) {
                $join->on('host_eq.EventID', '=', $eventIdColumn)
                    ->on('host_eq.QetaaID', '=', 'gq_srv.QetaaID');
            })
            ->where(function ($hostScope) {
                $hostScope->where('host_eq.AudienceScope', self::ALL)
                    ->orWhereNull('host_eq.AudienceScope');
            })
            ->whereNotIn('host_eq.QetaaID', LiveFormQetaaResolver::FROZEN_QETAA_IDS);

        if (is_int($personIdColumn)) {
            $group->where('pg_srv.PersonID', $personIdColumn);
        } else {
            $group->whereColumn('pg_srv.PersonID', $personIdColumn);
        }
    }

    private function hasScopeColumn(): bool
    {
        return Schema::hasTable('EventQetaa') && Schema::hasColumn('EventQetaa', 'AudienceScope');
    }
}
