<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Carbon\Carbon;

class AuditTrailConfig extends Component
{
    use WithPagination;

    // Filters
    public $startDate;
    public $endDate;
    public $selectedTable = '';
    public $selectedUser = '';
    public $selectedAction = '';
    
    // Settings
    public $retentionDays = 90;
    public $enableLogging = true;
    
    // Loading states
    public $isLoading = false;
    public $isCleaningUp = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        // Set default date range to last 7 days
        $this->startDate = now()->subDays(7)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $auditLogs = $this->getAuditLogs();
        $stats = $this->getAuditStats();
        $availableTables = $this->getAvailableTables();
        $availableUsers = $this->getAvailableUsers();
        
        return view('livewire.audit-trail-config', [
            'auditLogs' => $auditLogs,
            'stats' => $stats,
            'availableTables' => $availableTables,
            'availableUsers' => $availableUsers,
        ]);
    }

    public function getAuditLogs()
    {
        $query = DB::table('audits')
            ->select([
                'id',
                'user_type',
                'user_id',
                'event',
                'auditable_type',
                'auditable_id',
                'old_values',
                'new_values',
                'url',
                'ip_address',
                'user_agent',
                'created_at'
            ])
            ->orderBy('created_at', 'desc');

        // Apply date filters
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Apply table filter
        if ($this->selectedTable) {
            $query->where('auditable_type', 'like', '%' . $this->selectedTable . '%');
        }

        // Apply user filter
        if ($this->selectedUser) {
            $query->where('user_id', $this->selectedUser);
        }

        // Apply action filter
        if ($this->selectedAction) {
            $query->where('event', $this->selectedAction);
        }

        return $query->paginate(20);
    }

    public function getAuditStats()
    {
        $totalLogs = DB::table('audits')->count();
        $logsToday = DB::table('audits')->whereDate('created_at', today())->count();
        $logsThisWeek = DB::table('audits')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $oldestLog = DB::table('audits')->orderBy('created_at', 'asc')->first();
        
        return [
            'total_logs' => $totalLogs,
            'logs_today' => $logsToday,
            'logs_this_week' => $logsThisWeek,
            'oldest_log_date' => $oldestLog ? Carbon::parse($oldestLog->created_at)->format('d/m/Y') : null,
            'size_estimate' => $totalLogs * 2 // Rough estimate in KB
        ];
    }

    public function getAvailableTables()
    {
        return DB::table('audits')
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->map(function ($type) {
                return ['value' => $type, 'label' => class_basename($type)];
            });
    }

    public function getAvailableUsers()
    {
        return DB::table('audits')
            ->join('users', 'audits.user_id', '=', 'users.id')
            ->select('users.id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get();
    }

    public function clearFilters()
    {
        $this->startDate = now()->subDays(7)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->selectedTable = '';
        $this->selectedUser = '';
        $this->selectedAction = '';
        $this->resetPage();
    }

    public function cleanupOldLogs()
    {
        $this->isCleaningUp = true;
        
        try {
            $cutoffDate = now()->subDays($this->retentionDays);
            $deletedCount = DB::table('audits')
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            LivewireAlert::title('Berhasil!')
                ->text("Berhasil menghapus {$deletedCount} log audit yang lama.")
                ->success()
                ->show();

        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal membersihkan log: ' . $e->getMessage())
                ->error()
                ->show();
        }
        
        $this->isCleaningUp = false;
    }

    public function exportLogs()
    {
        try {
            $logs = $this->getAuditLogs();
            $filename = 'audit-trail-' . now()->format('Y-m-d-H-i-s') . '.csv';
            
            return response()->streamDownload(function () use ($logs) {
                echo "Timestamp,User,Action,Table,Record ID,IP Address,Changes\n";
                
                foreach ($logs as $log) {
                    $user = $log->user_id ? "User {$log->user_id}" : 'System';
                    $changes = json_encode($log->new_values);
                    
                    echo sprintf(
                        "%s,%s,%s,%s,%s,%s,%s\n",
                        $log->created_at,
                        $user,
                        $log->event,
                        class_basename($log->auditable_type),
                        $log->auditable_id,
                        $log->ip_address,
                        str_replace('"', '""', $changes)
                    );
                }
            }, $filename, [
                'Content-Type' => 'text/csv',
            ]);
            
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal mengekspor log: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function refreshLogs()
    {
        $this->resetPage();
        LivewireAlert::title('Refreshed!')
            ->text('Data audit trail telah diperbarui.')
            ->info()
            ->show();
    }
} 