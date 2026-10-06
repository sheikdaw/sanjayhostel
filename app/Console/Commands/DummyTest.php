<?php

namespace App\Console\Commands;

use App\Models\Resident;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DummyTest extends Command
{
    protected $signature = 'dummy:test {--resident=5 : Resident ID to vacate}';
    protected $description = 'Dummy command to test console + scheduler with resident vacate';

    public function handle(): int
    {
        $time       = now()->toDateTimeString();
        $residentId = (int) $this->option('resident');

        $this->info("🔔 DUMMY: dummy:test run aaguthu — {$time}");
        $this->newLine();

        // Resident load pannunga
        $resident = Resident::with(['hostel', 'room', 'bed'])->find($residentId);

        if (!$resident) {
            $this->error("❌ Resident ID {$residentId} kidaikkala!");
            Log::warning('🔔 DUMMY: resident not found', ['resident_id' => $residentId]);
            return self::FAILURE;
        }

        // Resident details console-la kattunga
        $this->line("👤 Resident Details (Before):");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID',                 $resident->id],
                ['Name',               $resident->name],
                ['Employee Code',      $resident->employee_code ?? '-'],
                ['Status',             $resident->status],
                ['Hostel',             $resident->hostel->hostel_name ?? '-'],
                ['Room',               $resident->room->room_no ?? '-'],
                ['Bed',                $resident->bed->bed_no ?? '-'],
                ['Access Disabled At', $resident->access_disabled_at ?? '-'],
            ]
        );

        // Already vacated-a nu check
        if ($resident->status === 'VACATED') {
            $this->warn("⚠️  Resident ID {$resident->id} already VACATED!");
            Log::info('🔔 DUMMY: resident already vacated', ['resident_id' => $resident->id]);
            return self::SUCCESS;
        }

        // Vacate pannunga (ESSL illama — DB mattum)
        $this->newLine();
        $this->line("🚫 Vacating resident ID {$resident->id}...");

        $resident->update([
            'status'             => 'VACATED',
            'vacate_date'        => now()->format('Y-m-d'),
            'access_disabled_at' => now(),
        ]);

        // Bed free pannunga
        if ($resident->bed_id) {
            \App\Models\Bed::where('id', $resident->bed_id)->update(['status' => 'VACANT']);
        }

        // Room status update
        if ($resident->room_id) {
            $room = \App\Models\Room::find($resident->room_id);
            if ($room) {
                $total    = $room->beds()->count();
                $occupied = $room->beds()->where('status', 'OCCUPIED')->count();

                if ($occupied === 0) {
                    $roomStatus = 'VACANT';
                } elseif ($occupied >= $total) {
                    $roomStatus = 'FULL';
                } else {
                    $roomStatus = 'PARTIAL';
                }
                $room->update(['status' => $roomStatus]);
            }
        }

        $resident->refresh();

        // After details
        $this->newLine();
        $this->line("👤 Resident Details (After):");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID',                 $resident->id],
                ['Name',               $resident->name],
                ['Status',             $resident->status],
                ['Vacate Date',        $resident->vacate_date ?? '-'],
                ['Access Disabled At', $resident->access_disabled_at ?? '-'],
            ]
        );

        $this->alert("✅ Resident ID {$resident->id} VACATED successfully!");

        // Log output
        Log::info('🔔 DUMMY: resident vacated', [
            'time'        => $time,
            'resident_id' => $resident->id,
            'name'        => $resident->name,
            'status'      => $resident->status,
        ]);

        return self::SUCCESS;
    }
}
