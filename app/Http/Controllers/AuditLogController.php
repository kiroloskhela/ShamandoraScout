<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'created_at' => optional($log->created_at)->format('Y-m-d H:i:s'),
                'actor_name' => $log->actor_name ?: '—',
                'person_id' => $log->person_id ?: '—',
                'action' => $log->action,
                'path' => $log->path,
                'response_status' => $log->response_status ?? '—',
                'ip' => $log->ip,
            ];
        });

        return view('audit-logs.index', compact('logs'));
    }
}
