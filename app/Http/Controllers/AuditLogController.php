<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\Permissions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * @var list<string>
     */
    private const FILTERS = ['q', 'category', 'event', 'status', 'severity', 'from', 'to'];

    public function index(Request $request): View
    {
        auth()->user()->requirePermission(Permissions::AUDIT_LOG_VIEW);

        $term = $request->string('q')->trim()->value();

        $logs = AuditLog::query()
            ->with('actor')
            ->when($term !== '', function (Builder $query) use ($term): void {
                $query->where(function (Builder $q) use ($term): void {
                    $q->where('event', 'like', '%'.$term.'%')
                        ->orWhere('subject', 'like', '%'.$term.'%')
                        ->orWhere('actor_name', 'like', '%'.$term.'%');
                });
            })
            ->when($request->filled('category'), fn (Builder $q, string $value) => $q->where('category', $value))
            ->when($request->filled('event'), fn (Builder $q, string $value) => $q->where('event', $value))
            ->when($request->filled('status'), fn (Builder $q, string $value) => $q->where('status', $value))
            ->when($request->filled('severity'), fn (Builder $q, string $value) => $q->where('severity', $value))
            ->when($request->filled('from'), fn (Builder $q, string $value) => $q->whereDate('created_at', '>=', $value))
            ->when($request->filled('to'), fn (Builder $q, string $value) => $q->whereDate('created_at', '<=', $value))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('audit-logs.index', [
            'logs' => $logs,
            'filters' => $request->only(self::FILTERS),
            'categories' => AuditLog::query()->distinct()->orderBy('category')->pluck('category')->filter()->values(),
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event')->values(),
            'statuses' => AuditLog::query()->distinct()->orderBy('status')->pluck('status')->filter()->values(),
            'severities' => AuditLog::query()->distinct()->orderBy('severity')->pluck('severity')->filter()->values(),
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        auth()->user()->requirePermission(Permissions::AUDIT_LOG_VIEW);

        return view('audit-logs.show', compact('auditLog'));
    }
}
