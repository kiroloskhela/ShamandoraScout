<?php

namespace App\Domain\Person;

/**
 * In-memory kinship graph built from PersonFamily links.
 *
 * A link row means "FamilyMember F is Person X's <relation>". Persons are `p{id}`
 * nodes and FamilyMembers are `f{id}` hubs; a hub that is also a registered person
 * collapses into that person's node so multi-hop inference (grandparents, cousins)
 * can walk through it. Relatives are derived in both directions: if Amgad is the
 * father of A and B and the paternal uncle of C, then A and B are siblings, C is
 * their cousin, and C's father is their uncle.
 */
final class FamilyGraph
{
    /** Bucket priority: a node lands in the first bucket that claims it. */
    private const BUCKETS = ['parents', 'siblings', 'children', 'partners', 'grandparents', 'uncles_aunts', 'nephews_nieces', 'cousins'];

    private const PARENT = ['أب' => 'm', 'أم' => 'f'];

    private const SIBLING = ['أخ' => 'm', 'أخت' => 'f'];

    private const GRANDPARENT = ['جد' => 'm', 'جدة' => 'f'];

    /** relation => [gender of F, side from X's (the nephew's) perspective] */
    private const UNCLE = ['عم' => ['m', 'paternal'], 'عمة' => ['f', 'paternal'], 'خال' => ['m', 'maternal'], 'خالة' => ['f', 'maternal']];

    /** "F is X's brother's son" ⇒ X is F's paternal uncle/aunt: side is from F's (the nephew's) perspective. */
    private const NEPHEW = ['ابن أخ' => ['m', 'paternal'], 'ابنة أخ' => ['f', 'paternal'], 'ابن أخت' => ['m', 'maternal'], 'ابنة أخت' => ['f', 'maternal']];

    /**
     * relation => [gender of F, side for X, side for F]. The side flips when the linking
     * siblings differ in gender: ابن خال means X's mother and F's father are siblings, so F
     * sees X through his father's sister, i.e. on the paternal side.
     */
    private const COUSIN = [
        'ابن عم' => ['m', 'paternal', 'paternal'], 'ابنة عم' => ['f', 'paternal', 'paternal'],
        'ابن عمة' => ['m', 'paternal', 'maternal'], 'ابنة عمة' => ['f', 'paternal', 'maternal'],
        'ابن خال' => ['m', 'maternal', 'paternal'], 'ابنة خال' => ['f', 'maternal', 'paternal'],
        'ابن خالة' => ['m', 'maternal', 'maternal'], 'ابنة خالة' => ['f', 'maternal', 'maternal'],
    ];

    private const PARTNER = ['زوج' => ['m', 'spouse'], 'زوجة' => ['f', 'spouse'], 'خطيب' => ['m', 'engaged'], 'خطيبة' => ['f', 'engaged']];

    /** @var array<string, array<string, true>> child => parents */
    private array $parents = [];

    /** @var array<string, array<string, true>> parent => children */
    private array $children = [];

    /** @var array<string, array<string, true>> symmetric */
    private array $siblings = [];

    /** @var array<string, array<string, true>> grandchild => grandparents */
    private array $grandparents = [];

    /** @var array<string, array<string, string>> nephew => uncle => side (nephew's perspective) */
    private array $uncles = [];

    /** @var array<string, array<string, string>> uncle => nephew => side (nephew's perspective) */
    private array $nephews = [];

    /** @var array<string, array<string, string>> node => cousin => side (node's perspective) */
    private array $cousins = [];

    /** @var array<string, array<string, string>> symmetric, node => partner => kind */
    private array $partners = [];

    /** @var array<string, array<string, true>> symmetric: a PersonFamily row directly joins the two nodes */
    private array $linked = [];

    /** @var array<string, string> node => 'm'|'f' */
    private array $gender = [];

    /** @var array<string, int> node => FamilyID (the hub row that names an unregistered relative) */
    private array $hubOf = [];

    /**
     * @param  iterable<array{PersonID:int, FamilyID:int, RelationName:string}|object>  $links
     * @param  array<int, int>  $hubToPerson  FamilyID => PersonID for hubs that are registered persons
     * @param  array<int, string|null>  $personGender  PersonID => 'Male'|'Female'
     */
    public function __construct(iterable $links, array $hubToPerson = [], array $personGender = [])
    {
        foreach ($personGender as $personId => $gender) {
            if ($gender === 'Male' || $gender === 'Female') {
                $this->gender['p'.$personId] = $gender === 'Male' ? 'm' : 'f';
            }
        }

        foreach ($hubToPerson as $familyId => $personId) {
            $this->hubOf['p'.$personId] ??= (int) $familyId;
        }

        foreach ($links as $link) {
            $link = (array) $link;
            $familyId = (int) $link['FamilyID'];
            $x = 'p'.(int) $link['PersonID'];
            $f = isset($hubToPerson[$familyId]) ? 'p'.$hubToPerson[$familyId] : 'f'.$familyId;

            if ($x === $f) {
                continue; // a person registered as their own relative
            }

            $this->hubOf[$f] ??= $familyId;
            $this->addLink($x, $f, (string) $link['RelationName']);
        }
    }

    /**
     * @return array<string, list<array{key:string, PersonID:?int, FamilyID:?int, gender:?string, side:?string, kind:?string, inferred:bool, label:string}>>
     */
    public function relativesOf(int $personId): array
    {
        $ego = 'p'.$personId;
        $found = array_fill_keys(self::BUCKETS, []);

        $add = function (string $bucket, string $key, ?string $side = null, ?string $kind = null) use (&$found, $ego): void {
            if ($key === $ego) {
                return;
            }
            $found[$bucket][$key] = [
                'side' => $found[$bucket][$key]['side'] ?? $side,
                'kind' => $found[$bucket][$key]['kind'] ?? $kind,
            ];
        };

        // Nuclear family: explicit parents, everyone sharing one of them, my children and partners.
        $parents = array_keys($this->parents[$ego] ?? []);

        foreach ($parents as $p) {
            $add('parents', $p);
        }

        foreach ($this->siblingsOf($ego) as $s) {
            $add('siblings', $s);
        }
        $siblings = array_keys($found['siblings']);

        foreach (array_keys($this->children[$ego] ?? []) as $k) {
            $add('children', $k);
            foreach (array_keys($this->parents[$k] ?? []) as $coParent) {
                $add('partners', $coParent, null, 'spouse');
            }
        }
        foreach ($this->partners[$ego] ?? [] as $w => $kind) {
            $add('partners', $w, null, $kind);
        }

        // Up one generation: explicit grandparents (their other children are my uncles/aunts),
        // then everything reachable through each parent.
        foreach (array_keys($this->grandparents[$ego] ?? []) as $g) {
            $add('grandparents', $g);
            foreach (array_keys($this->children[$g] ?? []) as $u) {
                $add('uncles_aunts', $u);
            }
        }

        foreach ($parents as $p) {
            $side = $this->sideVia($p);

            foreach (array_keys($this->parents[$p] ?? []) as $g) {
                $add('grandparents', $g, $side);
            }
            foreach ($this->siblingsOf($p) as $u) {
                $add('uncles_aunts', $u, $side);
            }
            // Someone calls my parent their uncle/aunt: they are my cousin, and the
            // parent of theirs who is my parent's sibling is my uncle/aunt.
            foreach ($this->nephews[$p] ?? [] as $cousin => $cousinSide) {
                $add('cousins', $cousin, $side);
                foreach (array_keys($this->parents[$cousin] ?? []) as $q) {
                    if (($this->gender[$q] ?? null) === ($cousinSide === 'paternal' ? 'm' : 'f')) {
                        $add('uncles_aunts', $q, $side);
                    }
                }
            }
        }

        // Explicit uncle/cousin rows: mine, plus my siblings' on the side we share
        // (a half-sibling's maternal uncle is not mine when we only share a father).
        foreach ($this->uncles[$ego] ?? [] as $u => $side) {
            $add('uncles_aunts', $u, $side);
        }
        foreach ($this->cousins[$ego] ?? [] as $c => $side) {
            $add('cousins', $c, $side);
        }
        $declared = $this->declaredSiblings($ego);
        foreach ($siblings as $s) {
            $sharedSides = isset($declared[$s])
                ? ['paternal', 'maternal']
                : array_map($this->sideVia(...), array_keys(array_intersect_key($this->parents[$s] ?? [], $this->parents[$ego] ?? [])));

            foreach ($this->uncles[$s] ?? [] as $u => $side) {
                if (in_array($side, $sharedSides, true)) {
                    $add('uncles_aunts', $u, $side);
                }
            }
            foreach ($this->cousins[$s] ?? [] as $c => $side) {
                if (in_array($side, $sharedSides, true)) {
                    $add('cousins', $c, $side);
                }
            }
        }

        // Down from there: uncles' children are cousins, siblings' children are nephews/nieces.
        foreach ($found['uncles_aunts'] as $u => $meta) {
            foreach (array_keys($this->children[$u] ?? []) as $c) {
                $add('cousins', $c, $meta['side']);
            }
        }

        foreach ($siblings as $s) {
            foreach (array_keys($this->children[$s] ?? []) as $n) {
                $add('nephews_nieces', $n);
            }
        }
        foreach (array_keys($this->nephews[$ego] ?? []) as $n) {
            $add('nephews_nieces', $n);
        }

        $seen = [];
        $out = [];
        foreach (self::BUCKETS as $bucket) {
            $out[$bucket] = [];
            foreach ($found[$bucket] as $key => $claim) {
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $out[$bucket][] = $this->node($ego, $bucket, $key, $claim);
            }
        }

        return $out;
    }

    /** @return array<string, true> siblings declared by أخ/أخت rows, directly or through a shared declared sibling; trusted as full siblings */
    private function declaredSiblings(string $node): array
    {
        $set = $this->siblings[$node] ?? [];
        foreach (array_keys($this->siblings[$node] ?? []) as $s) {
            $set += $this->siblings[$s] ?? [];
        }
        unset($set[$node]);

        return $set;
    }

    /** @return list<string> declared siblings plus everyone sharing one of my parents */
    private function siblingsOf(string $node): array
    {
        $set = $this->declaredSiblings($node);
        foreach (array_keys($this->parents[$node] ?? []) as $p) {
            $set += $this->children[$p] ?? [];
        }
        unset($set[$node]);

        return array_keys($set);
    }

    private function sideVia(string $parent): ?string
    {
        return match ($this->gender[$parent] ?? null) {
            'm' => 'paternal',
            'f' => 'maternal',
            default => null,
        };
    }

    private function addLink(string $x, string $f, string $relation): void
    {
        $this->linked[$x][$f] = $this->linked[$f][$x] = true;

        if (isset(self::PARENT[$relation])) {
            $this->parents[$x][$f] = $this->children[$f][$x] = true;
            $this->gender[$f] ??= self::PARENT[$relation];
        } elseif (isset(self::SIBLING[$relation])) {
            $this->siblings[$x][$f] = $this->siblings[$f][$x] = true;
            $this->gender[$f] ??= self::SIBLING[$relation];
        } elseif (isset(self::GRANDPARENT[$relation])) {
            $this->grandparents[$x][$f] = true;
            $this->gender[$f] ??= self::GRANDPARENT[$relation];
        } elseif (isset(self::UNCLE[$relation])) {
            [$gender, $side] = self::UNCLE[$relation];
            $this->uncles[$x][$f] = $this->nephews[$f][$x] = $side;
            $this->gender[$f] ??= $gender;
        } elseif (isset(self::NEPHEW[$relation])) {
            [$gender, $side] = self::NEPHEW[$relation];
            $this->nephews[$x][$f] = $this->uncles[$f][$x] = $side;
            $this->gender[$f] ??= $gender;
        } elseif (isset(self::COUSIN[$relation])) {
            [$gender, $sideForX, $sideForF] = self::COUSIN[$relation];
            $this->cousins[$x][$f] = $sideForX;
            $this->cousins[$f][$x] = $sideForF;
            $this->gender[$f] ??= $gender;
        } elseif (isset(self::PARTNER[$relation])) {
            [$gender, $kind] = self::PARTNER[$relation];
            $this->partners[$x][$f] = $this->partners[$f][$x] = $kind;
            $this->gender[$f] ??= $gender;
        }
    }

    /**
     * @param  array{side:?string, kind:?string}  $claim
     * @return array{key:string, PersonID:?int, FamilyID:?int, gender:?string, side:?string, kind:?string, inferred:bool, label:string}
     */
    private function node(string $ego, string $bucket, string $key, array $claim): array
    {
        $gender = $this->gender[$key] ?? null;

        return $claim + [
            'key' => $key,
            'PersonID' => $key[0] === 'p' ? (int) substr($key, 1) : null,
            'FamilyID' => $this->hubOf[$key] ?? null,
            'gender' => $gender,
            'inferred' => ! isset($this->linked[$ego][$key]),
            'label' => self::label($bucket, $gender, $claim['side'], $claim['kind']),
        ];
    }

    /** Translation key for the badge, e.g. "Paternal uncle", "Maternal grandmother", "Brother/sister". */
    private static function label(string $bucket, ?string $gender, ?string $side, ?string $kind): string
    {
        $pick = fn (string $m, string $f, string $any) => match ($gender) {
            'm' => $m,
            'f' => $f,
            default => $any,
        };
        $sided = fn (string $base) => $side ? ucfirst($side).' '.lcfirst($base) : $base;

        return match ($bucket) {
            'parents' => $pick('Father', 'Mother', 'Parent'),
            'siblings' => $pick('Brother', 'Sister', 'Brother/sister'),
            'children' => $pick('Son', 'Daughter', 'Son/daughter'),
            'partners' => $kind === 'engaged' ? $pick('Fiancé', 'Fiancée', 'Fiancé(e)') : $pick('Husband', 'Wife', 'Spouse'),
            'grandparents' => $sided($pick('Grandfather', 'Grandmother', 'Grandfather/grandmother')),
            'uncles_aunts' => $sided($pick('Uncle', 'Aunt', 'Uncle/aunt')),
            'nephews_nieces' => $pick('Nephew', 'Niece', 'Nephew/niece'),
            'cousins' => $sided('Cousin'),
        };
    }
}
