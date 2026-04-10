<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonTreeController extends Controller
{
    public function index(Request $request)
    {
        $persons = DB::table('PersonInformation')
            ->select(
                'PersonID',
                'RaqamQawmy',
                DB::raw("CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) as FullName")
            )
            ->orderBy('FirstName')
            ->get();

        $selectedPerson = null;
        $tree = null;

        if ($request->filled('person_id')) {
            $selectedPerson = $this->getPersonBasic((int) $request->person_id);

            if ($selectedPerson) {
                $tree = $this->buildFamilyTree((int) $request->person_id);
            }
        }

        return view('person-tree.index', [
            'persons' => $persons,
            'selectedPerson' => $selectedPerson,
            'tree' => $tree
        ]);
    }

    private function buildFamilyTree(int $personId): array
    {
        $person = $this->getPersonBasic($personId);

        $parents = $this->getParents($personId);
        $father = $parents['father'];
        $mother = $parents['mother'];

        $siblings = $this->getSiblings($personId);
        $children = $this->getChildrenOfPerson($personId);

        $siblingIds = collect($siblings)->pluck('PersonID')->filter()->values()->toArray();

$children = collect($children)
    ->reject(function ($child) use ($siblingIds) {
        return in_array($child['PersonID'] ?? null, $siblingIds, true);
    })
    ->values()
    ->toArray();
        $partners = $this->getPartners($personId);
        $directGrandParents = $this->getDirectGrandParents($personId);
        $directUnclesAunts = $this->getDirectUnclesAunts($personId);
        $nephewsNieces = $this->getNephewsAndNieces($personId, $siblings);

        $fatherLine = $this->getParentLineData($father, 'paternal');
        $motherLine = $this->getParentLineData($mother, 'maternal');

        $allUnclesAunts = collect(array_merge(
            $directUnclesAunts,
            $fatherLine['uncles_aunts'],
            $motherLine['uncles_aunts']
        ))
            ->unique(function ($item) {
                return ($item['mapped_person_id'] ?? '') . '|' . ($item['FamilyID'] ?? '');
            })
            ->values()
            ->toArray();

        $allCousins = collect(array_merge(
            $fatherLine['cousins'],
            $motherLine['cousins'],
            $this->getCousinsFromDirectUnclesAunts($directUnclesAunts, $personId)
        ))
            ->unique('PersonID')
            ->values()
            ->toArray();

        return [
            'person' => $person,

            'father' => $father,
            'mother' => $mother,

            'siblings' => $siblings,
            'children' => $children,
            'wives' => $partners['wives'],
            'husbands' => $partners['husbands'],
            'fiancees' => $partners['fiancees'],
            'fiances' => $partners['fiances'],
            'partners' => $partners['all'],

            'direct_grandfathers' => $directGrandParents['grandfathers'],
            'direct_grandmothers' => $directGrandParents['grandmothers'],

            'paternal_grandfather' => $fatherLine['grandfather'],
            'paternal_grandmother' => $fatherLine['grandmother'],
            'maternal_grandfather' => $motherLine['grandfather'],
            'maternal_grandmother' => $motherLine['grandmother'],

            'direct_uncles_aunts' => $directUnclesAunts,
            'paternal_uncles_aunts' => $fatherLine['uncles_aunts'],
            'maternal_uncles_aunts' => $motherLine['uncles_aunts'],
            'all_uncles_aunts' => $allUnclesAunts,

            'paternal_cousins' => $fatherLine['cousins'],
            'maternal_cousins' => $motherLine['cousins'],
            'all_cousins' => $allCousins,

            'nephews_nieces' => $nephewsNieces,
        ];
    }

    private function getPersonBasic(int $personId)
    {
        return DB::table('PersonInformation')
            ->where('PersonID', $personId)
            ->select(
                'PersonID',
                'RaqamQawmy',
                DB::raw("CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) as FullName")
            )
            ->first();
    }

    private function getParents(int $personId): array
    {
        $rows = DB::table('PersonFamily as pf')
            ->join('FamilyMembers as fm', 'fm.FamilyID', '=', 'pf.FamilyID')
            ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->where('pf.PersonID', $personId)
            ->whereIn('r.RelationName', ['أب', 'أم'])
            ->select(
                'fm.FamilyID',
                'fm.RaqamQawmy',
                'r.RelationName',
                DB::raw("CONCAT_WS(' ', fm.FirstName, fm.SecondName, fm.ThirdName, fm.FourthName) as FullName")
            )
            ->get();

        $father = null;
        $mother = null;

        foreach ($rows as $row) {
            if ($row->RelationName === 'أب') {
                $father = $this->familyMemberNode($row);
            }

            if ($row->RelationName === 'أم') {
                $mother = $this->familyMemberNode($row);
            }
        }

        return [
            'father' => $father,
            'mother' => $mother,
        ];
    }

    private function getSiblings(int $personId): array
    {
        $parentFamilyIds = DB::table('PersonFamily as pf')
            ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->where('pf.PersonID', $personId)
            ->whereIn('r.RelationName', ['أب', 'أم'])
            ->pluck('pf.FamilyID')
            ->unique()
            ->values()
            ->toArray();

        if (empty($parentFamilyIds)) {
            return [];
        }

        $siblings = DB::table('PersonFamily as pf')
            ->join('PersonInformation as pi', 'pi.PersonID', '=', 'pf.PersonID')
            ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->whereIn('pf.FamilyID', $parentFamilyIds)
            ->whereIn('r.RelationName', ['أب', 'أم'])
            ->where('pf.PersonID', '!=', $personId)
            ->select(
                'pi.PersonID',
                'pi.RaqamQawmy',
                DB::raw("CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) as FullName")
            )
            ->distinct()
            ->get();

        return $siblings->map(function ($row) {
            return [
                'type' => 'person',
                'PersonID' => $row->PersonID,
                'FullName' => $row->FullName,
                'RaqamQawmy' => $row->RaqamQawmy,
                'RelationName' => 'أخ / أخت',
            ];
        })->values()->toArray();
    }

private function getChildrenOfPerson(int $personId): array
{
    $person = $this->getPersonBasic($personId);

    if (!$person) {
        return [];
    }

    $familyMemberIds = DB::table('FamilyMembers')
        ->where(function ($query) use ($person) {
            if (!empty($person->RaqamQawmy)) {
                $query->orWhere('RaqamQawmy', $person->RaqamQawmy);
            }

            $query->orWhereRaw(
                "CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) = ?",
                [$person->FullName]
            );
        })
        ->pluck('FamilyID')
        ->unique()
        ->values()
        ->toArray();

    if (empty($familyMemberIds)) {
        return [];
    }

    $children = DB::table('PersonFamily as pf')
        ->join('PersonInformation as pi', 'pi.PersonID', '=', 'pf.PersonID')
        ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
        ->whereIn('pf.FamilyID', $familyMemberIds)
        ->whereIn('r.RelationName', ['أب', 'أم'])
        ->where('pf.PersonID', '!=', $personId)
        ->select(
            'pi.PersonID',
            'pi.RaqamQawmy',
            DB::raw("CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) as FullName")
        )
        ->distinct()
        ->get();

    return $children->map(function ($row) {
        return [
            'type' => 'person',
            'PersonID' => $row->PersonID,
            'FullName' => $row->FullName,
            'RaqamQawmy' => $row->RaqamQawmy,
            'RelationName' => 'ابن / ابنة',
        ];
    })->values()->toArray();
}

private function getPartners(int $personId): array
{
    $rows = DB::table('PersonFamily as pf')
        ->join('FamilyMembers as fm', 'fm.FamilyID', '=', 'pf.FamilyID')
        ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
        ->where('pf.PersonID', $personId)
        ->whereIn('r.RelationName', ['زوج', 'زوجة', 'خطيب', 'خطيبة'])
        ->select(
            'fm.FamilyID',
            'fm.RaqamQawmy',
            'r.RelationName',
            DB::raw("CONCAT_WS(' ', fm.FirstName, fm.SecondName, fm.ThirdName, fm.FourthName) as FullName")
        )
        ->get();

    $wives = [];
    $husbands = [];
    $fiancees = [];
    $fiances = [];

    foreach ($rows as $row) {
        $node = $this->familyMemberNode($row);

        if ($row->RelationName === 'زوجة') {
            $wives[] = $node;
        } elseif ($row->RelationName === 'زوج') {
            $husbands[] = $node;
        } elseif ($row->RelationName === 'خطيبة') {
            $fiancees[] = $node;
        } elseif ($row->RelationName === 'خطيب') {
            $fiances[] = $node;
        }
    }

    return [
        'wives' => collect($wives)->unique('FamilyID')->values()->toArray(),
        'husbands' => collect($husbands)->unique('FamilyID')->values()->toArray(),
        'fiancees' => collect($fiancees)->unique('FamilyID')->values()->toArray(),
        'fiances' => collect($fiances)->unique('FamilyID')->values()->toArray(),
        'all' => collect(array_merge($wives, $husbands, $fiancees, $fiances))
            ->unique('FamilyID')
            ->values()
            ->toArray(),
    ];
}
    private function getParentLineData($parentNode, string $side = null): array
    {
        if (!$parentNode || empty($parentNode['mapped_person_id'])) {
            return [
                'grandfather' => null,
                'grandmother' => null,
                'uncles_aunts' => [],
                'cousins' => [],
            ];
        }

        $parentPersonId = (int) $parentNode['mapped_person_id'];

        $grandParents = $this->getParents($parentPersonId);

        $grandfather = $grandParents['father'];
        $grandmother = $grandParents['mother'];

        $unclesAunts = $this->getSiblingsOfPersonAsFamilyMembers($parentPersonId, $side);
        $cousins = [];

        foreach ($unclesAunts as $relative) {
            if (!empty($relative['FamilyID'])) {
                $children = $this->getChildrenOfFamilyMember((int) $relative['FamilyID']);
                foreach ($children as $child) {
                    $cousins[] = $child;
                }
            }
        }

        $cousins = collect($cousins)
            ->unique('PersonID')
            ->values()
            ->toArray();

        return [
            'grandfather' => $grandfather,
            'grandmother' => $grandmother,
            'uncles_aunts' => $unclesAunts,
            'cousins' => $cousins,
        ];
    }

    private function getSiblingsOfPersonAsFamilyMembers(int $personId, ?string $side = null): array
    {
        $rows = DB::table('PersonFamily as pf')
            ->join('FamilyMembers as fm', 'fm.FamilyID', '=', 'pf.FamilyID')
            ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->where('pf.PersonID', $personId)
            ->whereIn('r.RelationName', ['أخ', 'أخت', 'عم', 'عمة', 'خال', 'خالة'])
            ->select(
                'fm.FamilyID',
                'fm.RaqamQawmy',
                'r.RelationName',
                DB::raw("CONCAT_WS(' ', fm.FirstName, fm.SecondName, fm.ThirdName, fm.FourthName) as FullName")
            )
            ->get();

        $result = $rows->map(function ($row) {
            $mappedPerson = $this->findPersonByFamilyMember($row);

            return [
                'type' => 'family_member',
                'FamilyID' => $row->FamilyID,
                'FullName' => $row->FullName,
                'RaqamQawmy' => $row->RaqamQawmy,
                'RelationName' => $row->RelationName,
                'mapped_person_id' => $mappedPerson?->PersonID,
            ];
        })->values()->toArray();

        if ($side === 'paternal') {
            $result = array_values(array_filter($result, function ($item) {
                return in_array($item['RelationName'], ['أخ', 'أخت', 'عم', 'عمة'], true);
            }));
        }

        if ($side === 'maternal') {
            $result = array_values(array_filter($result, function ($item) {
                return in_array($item['RelationName'], ['أخ', 'أخت', 'خال', 'خالة'], true);
            }));
        }

        return $result;
    }

    private function getChildrenOfFamilyMember(int $familyId): array
    {
        $rows = DB::table('PersonFamily as pf')
            ->join('PersonInformation as pi', 'pi.PersonID', '=', 'pf.PersonID')
            ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->where('pf.FamilyID', $familyId)
            ->whereIn('r.RelationName', ['أب', 'أم'])
            ->select(
                'pi.PersonID',
                'pi.RaqamQawmy',
                DB::raw("CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) as FullName")
            )
            ->distinct()
            ->get();

        return $rows->map(function ($row) {
            return [
                'type' => 'person',
                'PersonID' => $row->PersonID,
                'FullName' => $row->FullName,
                'RaqamQawmy' => $row->RaqamQawmy,
            ];
        })->values()->toArray();
    }

    private function familyMemberNode($familyMemberRow): array
    {
        $mappedPerson = $this->findPersonByFamilyMember($familyMemberRow);

        return [
            'type' => 'family_member',
            'FamilyID' => $familyMemberRow->FamilyID,
            'FullName' => $familyMemberRow->FullName,
            'RaqamQawmy' => $familyMemberRow->RaqamQawmy,
            'RelationName' => $familyMemberRow->RelationName,
            'mapped_person_id' => $mappedPerson?->PersonID,
        ];
    }

    private function findPersonByFamilyMember($familyMemberRow)
    {
        if (!empty($familyMemberRow->RaqamQawmy)) {
            $person = DB::table('PersonInformation')
                ->where('RaqamQawmy', $familyMemberRow->RaqamQawmy)
                ->select('PersonID', 'RaqamQawmy')
                ->first();

            if ($person) {
                return $person;
            }
        }

        if (!empty($familyMemberRow->FullName)) {
            $person = DB::table('PersonInformation')
                ->whereRaw("CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) = ?", [$familyMemberRow->FullName])
                ->select('PersonID', 'RaqamQawmy')
                ->first();

            if ($person) {
                return $person;
            }
        }

        return null;
    }

    private function getDirectGrandParents(int $personId): array
    {
        $rows = DB::table('PersonFamily as pf')
            ->join('FamilyMembers as fm', 'fm.FamilyID', '=', 'pf.FamilyID')
            ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->where('pf.PersonID', $personId)
            ->whereIn('r.RelationName', ['جد', 'جدة'])
            ->select(
                'fm.FamilyID',
                'fm.RaqamQawmy',
                'r.RelationName',
                DB::raw("CONCAT_WS(' ', fm.FirstName, fm.SecondName, fm.ThirdName, fm.FourthName) as FullName")
            )
            ->get();

        $grandfathers = [];
        $grandmothers = [];

        foreach ($rows as $row) {
            $node = $this->familyMemberNode($row);

            if ($row->RelationName === 'جد') {
                $grandfathers[] = $node;
            }

            if ($row->RelationName === 'جدة') {
                $grandmothers[] = $node;
            }
        }

        return [
            'grandfathers' => collect($grandfathers)->unique('FamilyID')->values()->toArray(),
            'grandmothers' => collect($grandmothers)->unique('FamilyID')->values()->toArray(),
        ];
    }

    private function getDirectUnclesAunts(int $personId): array
    {
        $rows = DB::table('PersonFamily as pf')
            ->join('FamilyMembers as fm', 'fm.FamilyID', '=', 'pf.FamilyID')
            ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->where('pf.PersonID', $personId)
            ->whereIn('r.RelationName', ['عم', 'عمة', 'خال', 'خالة'])
            ->select(
                'fm.FamilyID',
                'fm.RaqamQawmy',
                'r.RelationName',
                DB::raw("CONCAT_WS(' ', fm.FirstName, fm.SecondName, fm.ThirdName, fm.FourthName) as FullName")
            )
            ->get();

        return $rows->map(function ($row) {
            return $this->familyMemberNode($row);
        })->unique('FamilyID')->values()->toArray();
    }

    private function getCousinsFromDirectUnclesAunts(array $directUnclesAunts, int $personId): array
    {
        $cousins = [];

        foreach ($directUnclesAunts as $relative) {
            if (!empty($relative['FamilyID'])) {
                $children = $this->getChildrenOfFamilyMember((int) $relative['FamilyID']);
                foreach ($children as $child) {
                    if ((int) $child['PersonID'] !== $personId) {
                        $cousins[] = $child;
                    }
                }
            }
        }

        return collect($cousins)->unique('PersonID')->values()->toArray();
    }

    private function getNephewsAndNieces(int $personId, array $siblings): array
    {
        $children = [];

        foreach ($siblings as $sibling) {
            if (!empty($sibling['PersonID'])) {
                $siblingChildren = $this->getChildrenOfPerson((int) $sibling['PersonID']);
                foreach ($siblingChildren as $child) {
                    if ((int) $child['PersonID'] !== $personId) {
                        $child['RelationName'] = 'ابن / ابنة أخ أو أخت';
                        $children[] = $child;
                    }
                }
            }
        }

        return collect($children)->unique('PersonID')->values()->toArray();
    }
}