<?php

namespace App\Domain\WhatsApp;

use App\Support\LikeSearch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Filterable person query for WhatsApp campaign recipient picking.
 */
class CampaignRecipientQuery
{
    /**
     * @param  array{
     *   q?: ?string,
     *   gender?: ?string,
     *   qetaa_id?: int|null,
     *   group_id?: int|null,
     *   manteqa_id?: int|null,
     *   district_id?: int|null,
     *   has_whatsapp?: bool|null,
     *   person_ids?: list<int>|null,
     *   exclude_blocked?: bool
     * }  $filters
     */
    public function search(array $filters = [], int $limit = 200): Collection
    {
        $bindings = [];
        $wheres = ['1=1'];

        $hasConsentCols = Schema::hasColumn('PersonPhoneNumbers', 'WhatsAppConsent');

        if (! empty($filters['q'])) {
            $fragment = $this->buildTextSearchFragment((string) $filters['q']);
            if ($fragment !== null) {
                $wheres[] = '('.$fragment['sql'].')';
                $bindings = array_merge($bindings, $fragment['bindings']);
            }
        }

        if (! empty($filters['gender'])) {
            $wheres[] = 'pi.Gender = ?';
            $bindings[] = $filters['gender'];
        }

        $qetaaIds = $this->normalizeIdList($filters['qetaa_ids'] ?? null, $filters['qetaa_id'] ?? null);
        if ($qetaaIds !== []) {
            $placeholders = implode(',', array_fill(0, count($qetaaIds), '?'));
            $wheres[] = "EXISTS (SELECT 1 FROM PersonQetaa pq2 WHERE pq2.PersonID = pi.PersonID AND pq2.QetaaID IN ({$placeholders}))";
            $bindings = array_merge($bindings, $qetaaIds);
        }

        $groupIds = $this->normalizeIdList($filters['group_ids'] ?? null, $filters['group_id'] ?? null);
        if ($groupIds !== []) {
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $wheres[] = "EXISTS (SELECT 1 FROM PersonGroup pg2 WHERE pg2.PersonID = pi.PersonID AND pg2.GroupID IN ({$placeholders}))";
            $bindings = array_merge($bindings, $groupIds);
        }

        if (! empty($filters['manteqa_id'])) {
            $wheres[] = 'EXISTS (SELECT 1 FROM PersonalPhysicalAddress addr WHERE addr.PersonID = pi.PersonID AND addr.ManteqaID = ?)';
            $bindings[] = (int) $filters['manteqa_id'];
        }

        if (! empty($filters['district_id'])) {
            $wheres[] = 'EXISTS (SELECT 1 FROM PersonalPhysicalAddress addr2 WHERE addr2.PersonID = pi.PersonID AND addr2.DistrictID = ?)';
            $bindings[] = (int) $filters['district_id'];
        }

        if (array_key_exists('has_whatsapp', $filters) && $filters['has_whatsapp'] !== null) {
            if ($filters['has_whatsapp']) {
                $wheres[] = "(ppn.IsOPersonalPhoneNumberHavingWhatsapp IN ('1', 'true', 'yes', 'نعم') OR ppn.IsOPersonalPhoneNumberHavingWhatsapp = 1)";
            } else {
                $wheres[] = "(ppn.IsOPersonalPhoneNumberHavingWhatsapp IS NULL OR ppn.IsOPersonalPhoneNumberHavingWhatsapp IN ('0', 'false', 'no', 'لا') OR ppn.IsOPersonalPhoneNumberHavingWhatsapp = 0)";
            }
        }

        if (! empty($filters['person_ids']) && is_array($filters['person_ids'])) {
            $ids = array_values(array_filter(array_map('intval', $filters['person_ids'])));
            if ($ids !== []) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $wheres[] = "pi.PersonID IN ({$placeholders})";
                $bindings = array_merge($bindings, $ids);
            }
        }

        $excludeBlocked = $filters['exclude_blocked'] ?? true;
        if ($excludeBlocked) {
            $wheres[] = 'NOT EXISTS (SELECT 1 FROM PersonBlackList bl WHERE bl.PersonID = pi.PersonID)';
            if ($hasConsentCols) {
                $wheres[] = '(ppn.DoNotContact IS NULL OR ppn.DoNotContact = 0)';
                $wheres[] = '(ppn.WhatsAppConsent IS NULL OR ppn.WhatsAppConsent = 1)';
            }
        }

        $wheres[] = "ppn.PersonPersonalMobileNumber IS NOT NULL AND TRIM(ppn.PersonPersonalMobileNumber) <> ''";

        $whereSql = implode(' AND ', $wheres);
        $limit = max(1, min(2000, $limit));

        $sql = "
            SELECT
                pi.PersonID,
                pi.ShamandoraCode,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                pi.Gender,
                ppn.PersonPersonalMobileNumber,
                (
                    SELECT q.QetaaName
                    FROM PersonQetaa pq
                    INNER JOIN Qetaa q ON q.QetaaID = pq.QetaaID
                    WHERE pq.PersonID = pi.PersonID
                    ORDER BY q.QetaaName ASC
                    LIMIT 1
                ) AS QetaaName
            FROM PersonInformation pi
            INNER JOIN PersonPhoneNumbers ppn ON ppn.PersonID = pi.PersonID
            WHERE {$whereSql}
            ORDER BY pi.FirstName ASC, pi.PersonID ASC
            LIMIT {$limit}
        ";

        return collect(DB::select($sql, $bindings))->map(function ($row) {
            $row->full_name = trim(implode(' ', array_filter([
                $row->FirstName ?? null,
                $row->SecondName ?? null,
                $row->ThirdName ?? null,
                $row->FourthName ?? null,
            ])));

            return $row;
        });
    }

    /**
     * Count matching people (for select-all-matching UI).
     *
     * @param  array<string, mixed>  $filters
     */
    public function count(array $filters = []): int
    {
        return $this->search($filters, 2000)->count();
    }

    /**
     * @param  mixed  $list
     * @param  mixed  $single
     * @return list<int>
     */
    private function normalizeIdList(mixed $list, mixed $single = null): array
    {
        $ids = [];
        if (is_array($list)) {
            $ids = $list;
        } elseif ($single !== null && $single !== '' && $single !== false) {
            $ids = [$single];
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
    }

    /**
     * Flexible name / code / phone match that only uses aliases present in the query.
     *
     * @return array{sql: string, bindings: list<string>}|null
     */
    private function buildTextSearchFragment(string $raw): ?array
    {
        $term = LikeSearch::term($raw);
        if ($term === null) {
            return null;
        }

        $nameColumns = [
            'pi.FirstName',
            'pi.SecondName',
            'pi.ThirdName',
            'pi.FourthName',
        ];
        $identityColumns = [
            'CAST(pi.PersonID AS CHAR)',
            'pi.ShamandoraCode',
            "CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName)",
        ];
        $phoneColumns = LikeSearch::personPhoneColumns('ppn');

        $parts = [];
        $bindings = [];

        // Word mode: each word must match at least one name part (AND of ORs).
        $words = LikeSearch::words($term);
        if ($words !== []) {
            $wordGroups = [];
            foreach ($words as $word) {
                $like = LikeSearch::wildcard($word);
                $ors = [];
                foreach ($nameColumns as $column) {
                    $ors[] = $column.' LIKE ?';
                    $bindings[] = $like;
                }
                $wordGroups[] = '('.implode(' OR ', $ors).')';
            }
            $parts[] = '('.implode(' AND ', $wordGroups).')';
        }

        // Full-term identity / phone match.
        $identity = LikeSearch::sqlFlexibleOr(
            array_merge($identityColumns, $nameColumns),
            $term,
            $phoneColumns,
        );
        $parts[] = $identity['sql'];
        $bindings = array_merge($bindings, $identity['bindings']);

        return [
            'sql' => implode(' OR ', $parts),
            'bindings' => $bindings,
        ];
    }
}
