<?php

namespace App\Http\Controllers;

use App\Domain\Person\FamilyGraph;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PersonTreeController extends Controller
{
    private const FULL_NAME = "CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName)";

    /** Hub hops loaded around the selected person; 3 reaches cousins via grandparents. */
    private const HOPS = 3;

    public function index(Request $request)
    {
        $persons = DB::table('PersonInformation')
            ->select('PersonID', 'RaqamQawmy', 'Gender', DB::raw(self::FULL_NAME.' as FullName'))
            ->orderBy('FirstName')
            ->get();

        $selectedPerson = null;
        $tree = null;

        if ($request->filled('person_id')) {
            $personId = (int) $request->person_id;
            $selectedPerson = $persons->firstWhere('PersonID', $personId);

            if ($selectedPerson) {
                $tree = $this->buildFamilyTree($personId, $persons->keyBy('PersonID'));
            }
        }

        return view('person-tree.index', [
            'persons' => $persons,
            'selectedPerson' => $selectedPerson,
            'tree' => $tree,
        ]);
    }

    /**
     * @param  Collection<int, object>  $persons  keyed by PersonID
     * @return array<string, mixed>
     */
    private function buildFamilyTree(int $personId, Collection $persons): array
    {
        // ponytail: loads every FamilyMembers row (one per registered relative) to unify hubs
        // with persons in memory; switch to a whereIn on the visited hubs if this table grows large.
        $hubs = DB::table('FamilyMembers')
            ->select('FamilyID', 'RaqamQawmy', DB::raw(self::FULL_NAME.' as FullName'))
            ->get()
            ->keyBy('FamilyID');

        $hubToPerson = $this->mapHubsToPersons($hubs, $persons);
        $links = $this->loadLinks($personId, $hubToPerson);
        $graph = new FamilyGraph($links, $hubToPerson, $persons->map(fn ($p) => $p->Gender)->all());

        $tree = ['person' => $persons[$personId]];

        foreach ($graph->relativesOf($personId) as $bucket => $nodes) {
            $tree[$bucket] = array_values(array_filter(array_map(function (array $node) use ($persons, $hubs) {
                $row = $node['PersonID'] !== null ? ($persons[$node['PersonID']] ?? null) : ($hubs[$node['FamilyID']] ?? null);

                return $row ? $node + ['FullName' => $row->FullName, 'RaqamQawmy' => $row->RaqamQawmy] : null;
            }, $nodes)));
        }

        return $tree;
    }

    /**
     * A FamilyMember is the same human as a person when the national ID matches, or
     * failing that when the full name matches exactly one person.
     *
     * @param  Collection<int, object>  $hubs
     * @param  Collection<int, object>  $persons
     * @return array<int, int> FamilyID => PersonID
     */
    private function mapHubsToPersons(Collection $hubs, Collection $persons): array
    {
        $byRaqam = $persons->filter(fn ($p) => ! empty($p->RaqamQawmy))->groupBy('RaqamQawmy');
        $byName = $persons->groupBy('FullName');

        $map = [];
        foreach ($hubs as $hub) {
            $match = ! empty($hub->RaqamQawmy) ? $byRaqam->get($hub->RaqamQawmy) : null;
            $match ??= ($byName->get($hub->FullName)?->count() === 1) ? $byName->get($hub->FullName) : null;

            if ($match) {
                $map[(int) $hub->FamilyID] = (int) $match->min('PersonID');
            }
        }

        return $map;
    }

    /**
     * Breadth-first load of PersonFamily rows around the person: each hop collects the
     * hubs linked to (or unified with) the frontier persons, then every row on those hubs.
     *
     * @param  array<int, int>  $hubToPerson
     * @return Collection<int, object>
     */
    private function loadLinks(int $personId, array $hubToPerson): Collection
    {
        $hubsOfPerson = [];
        foreach ($hubToPerson as $familyId => $mappedPersonId) {
            $hubsOfPerson[$mappedPersonId][] = $familyId;
        }

        $links = collect();
        $seenHubs = [];
        $seenPersons = [$personId => true];
        $frontier = [$personId];

        for ($hop = 0; $hop < self::HOPS && $frontier; $hop++) {
            $hubIds = DB::table('PersonFamily')->whereIn('PersonID', $frontier)->pluck('FamilyID')->map(fn ($id) => (int) $id)->all();
            foreach ($frontier as $p) {
                array_push($hubIds, ...($hubsOfPerson[$p] ?? []));
            }
            $hubIds = array_values(array_diff(array_unique($hubIds), array_keys($seenHubs)));

            if (! $hubIds) {
                break;
            }
            $seenHubs += array_fill_keys($hubIds, true);

            $rows = DB::table('PersonFamily as pf')
                ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
                ->whereIn('pf.FamilyID', $hubIds)
                ->select('pf.PersonID', 'pf.FamilyID', 'r.RelationName')
                ->get();
            $links = $links->merge($rows);

            $personsOnHubs = array_intersect_key($hubToPerson, array_flip($hubIds));
            $frontier = [];
            foreach ([...$rows->pluck('PersonID')->all(), ...$personsOnHubs] as $p) {
                $p = (int) $p;
                if (! isset($seenPersons[$p])) {
                    $seenPersons[$p] = true;
                    $frontier[] = $p;
                }
            }
        }

        return $links;
    }
}
