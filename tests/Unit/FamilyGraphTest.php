<?php

namespace Tests\Unit;

use App\Domain\Person\FamilyGraph;
use PHPUnit\Framework\TestCase;

/**
 * Persons: 7 = Me, 10 = Mina, 20 = Ehab. Hubs: 1 = Amgad, 2 = Samir (Ehab's father).
 */
class FamilyGraphTest extends TestCase
{
    /** @return array{PersonID:int, FamilyID:int, RelationName:string} */
    private function link(int $person, int $hub, string $relation): array
    {
        return ['PersonID' => $person, 'FamilyID' => $hub, 'RelationName' => $relation];
    }

    /** @return array<string, list<string>> bucket => keys */
    private function keys(array $tree): array
    {
        return array_map(fn (array $nodes) => array_column($nodes, 'key'), array_filter($tree));
    }

    private function find(array $tree, string $bucket, string $key): array
    {
        foreach ($tree[$bucket] as $node) {
            if ($node['key'] === $key) {
                return $node;
            }
        }
        $this->fail("$key not in $bucket: ".json_encode($this->keys($tree)));
    }

    public function test_father_of_two_and_uncle_of_third_yields_siblings_cousin_and_uncle(): void
    {
        $graph = new FamilyGraph([
            $this->link(7, 1, 'أب'),
            $this->link(10, 1, 'أب'),
            $this->link(20, 1, 'عم'),
            $this->link(20, 2, 'أب'),
        ], [], [10 => 'Female', 20 => 'Male']);

        $me = $graph->relativesOf(7);
        $this->assertSame([
            'parents' => ['f1'],
            'siblings' => ['p10'],
            'uncles_aunts' => ['f2'],
            'cousins' => ['p20'],
        ], $this->keys($me));
        $this->assertSame('Father', $this->find($me, 'parents', 'f1')['label']);
        $this->assertFalse($this->find($me, 'parents', 'f1')['inferred']);
        $this->assertSame('Sister', $this->find($me, 'siblings', 'p10')['label']);
        $this->assertTrue($this->find($me, 'siblings', 'p10')['inferred']);
        $this->assertSame('Paternal uncle', $this->find($me, 'uncles_aunts', 'f2')['label']);
        $this->assertSame('Paternal cousin', $this->find($me, 'cousins', 'p20')['label']);

        $ehab = $graph->relativesOf(20);
        $this->assertSame([
            'parents' => ['f2'],
            'uncles_aunts' => ['f1'],
            'cousins' => ['p7', 'p10'],
        ], $this->keys($ehab));
        $this->assertFalse($this->find($ehab, 'uncles_aunts', 'f1')['inferred']);
    }

    public function test_maternal_uncle_link_points_at_the_cousins_mother(): void
    {
        // Ehab says Amgad is his خال (mother's brother) ⇒ Ehab's mother is my paternal aunt.
        $graph = new FamilyGraph([
            $this->link(7, 1, 'أب'),
            $this->link(20, 1, 'خال'),
            $this->link(20, 2, 'أب'),
            $this->link(20, 3, 'أم'),
        ]);

        $me = $graph->relativesOf(7);
        $this->assertSame(['f3'], $this->keys($me)['uncles_aunts']);
        $this->assertSame('Paternal aunt', $this->find($me, 'uncles_aunts', 'f3')['label']);
    }

    public function test_hub_that_is_a_person_lets_inference_climb_to_grandparents_and_cousins(): void
    {
        // Hub 3 is person 30 (my dad). Dad's father is hub 4 (also my explicit جد); person 40 also
        // calls hub 4 father (uncle); hub 5 is person 40; person 50 calls hub 5 father (cousin).
        $graph = new FamilyGraph([
            $this->link(7, 3, 'أب'),
            $this->link(7, 4, 'جد'),
            $this->link(30, 4, 'أب'),
            $this->link(40, 4, 'أب'),
            $this->link(50, 5, 'أب'),
        ], [3 => 30, 5 => 40], [30 => 'Male', 40 => 'Male']);

        $me = $graph->relativesOf(7);
        $this->assertSame([
            'parents' => ['p30'],
            'grandparents' => ['f4'],
            'uncles_aunts' => ['p40'],
            'cousins' => ['p50'],
        ], $this->keys($me));
        $this->assertSame(3, $this->find($me, 'parents', 'p30')['FamilyID']);
        $this->assertSame('Paternal grandfather', $this->find($me, 'grandparents', 'f4')['label']);
        $this->assertFalse($this->find($me, 'grandparents', 'f4')['inferred']);
        // Reached first through the explicit جد (no side) and again through dad: the side must survive.
        $this->assertSame('Paternal uncle', $this->find($me, 'uncles_aunts', 'p40')['label']);

        $dad = $graph->relativesOf(30);
        $this->assertSame([
            'parents' => ['f4'],
            'siblings' => ['p40'],
            'children' => ['p7'],
            'nephews_nieces' => ['p50'],
        ], $this->keys($dad));
    }

    public function test_half_sibling_shares_only_the_common_side(): void
    {
        // 10 shares my father; their mother, maternal uncle and maternal cousin are not mine,
        // but their paternal uncle is.
        $graph = new FamilyGraph([
            $this->link(7, 1, 'أب'),
            $this->link(10, 1, 'أب'),
            $this->link(10, 6, 'أم'),
            $this->link(10, 11, 'خال'),
            $this->link(10, 12, 'ابن خال'),
            $this->link(10, 13, 'عم'),
        ]);

        $this->assertSame([
            'parents' => ['f1'],
            'siblings' => ['p10'],
            'uncles_aunts' => ['f13'],
        ], $this->keys($graph->relativesOf(7)));
    }

    public function test_explicit_sibling_shares_both_sides(): void
    {
        $graph = new FamilyGraph([
            $this->link(7, 9, 'أخ'),
            $this->link(10, 9, 'أخ'),
            $this->link(10, 11, 'خال'),
        ]);

        $me = $graph->relativesOf(7);
        $this->assertSame(['siblings' => ['f9', 'p10'], 'uncles_aunts' => ['f11']], $this->keys($me));
        $this->assertSame('Maternal uncle', $this->find($me, 'uncles_aunts', 'f11')['label']);
    }

    public function test_co_parent_is_an_inferred_spouse_and_self_links_are_dropped(): void
    {
        $graph = new FamilyGraph([
            $this->link(7, 3, 'أب'),
            $this->link(7, 8, 'أم'),
            $this->link(30, 3, 'أخ'), // dad registered as his own brother: junk row
        ], [3 => 30]);

        $dad = $graph->relativesOf(30);
        $this->assertSame(['children' => ['p7'], 'partners' => ['f8']], $this->keys($dad));
        $wife = $this->find($dad, 'partners', 'f8');
        $this->assertSame('Wife', $wife['label']);
        $this->assertTrue($wife['inferred']);
    }

    public function test_closest_bucket_wins(): void
    {
        // Person 10 shares my father and is also (wrongly) recorded as my cousin.
        $graph = new FamilyGraph([
            $this->link(7, 1, 'أب'),
            $this->link(10, 1, 'أب'),
            $this->link(7, 9, 'ابن عم'),
        ], [9 => 10]);

        $this->assertSame(['parents' => ['f1'], 'siblings' => ['p10']], $this->keys($graph->relativesOf(7)));
    }
}
