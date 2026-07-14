<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\LikeSearch;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderByDesc('id');

        if ($request->filled('person_id')) {
            $query->where('person_id', (int) $request->input('person_id'));
        }

        if ($request->filled('method')) {
            $query->where('method', strtoupper((string) $request->input('method')));
        }

        $term = LikeSearch::fromRequest($request);
        if ($term !== null) {
            LikeSearch::applyOr($query, $term, ['path', 'action', 'route_name', 'actor_name']);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->paginate(50)->appends($request->query());

        return view('audit-logs.index', compact('logs'));
    }
}
