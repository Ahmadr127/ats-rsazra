<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune {--days= : Umur maksimum log dalam hari (opsional)}';

    protected $description = 'Hapus log audit yang lebih tua dari masa retensi';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('audit.retention_days', 90));

        if ($days < 1) {
            $this->error('Masa retensi minimal 1 hari.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $deleted = AuditLog::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("Log audit dihapus: {$deleted} baris (lebih tua dari {$days} hari).");

        return self::SUCCESS;
    }
}
