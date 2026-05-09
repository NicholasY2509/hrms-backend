<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\System\Models\Report;
use App\Modules\System\Jobs\ProcessExportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'format' => 'required|in:excel,pdf,csv,txt',
            'filters' => 'nullable|array'
        ]);

        $report = Report::create([
            'user_id' => auth()->id() ?? 1,
            'name' => $request->name,
            'type' => $request->type,
            'format' => $request->format,
            'filters' => $request->filters,
            'status' => 'pending'
        ]);

        ProcessExportJob::dispatch($report);

        return response()->json([
            'message' => 'Export request submitted successfully',
            'data' => $report
        ], 202);
    }

    public function show(Report $report)
    {
        $data = $report->toArray();
        
        if ($report->status === 'completed' && $report->file_path) {
            $diskName = config('filesystems.default') === 'local' ? 'local' : 'gcs';
            $disk = Storage::disk($diskName);
            
            try {
                $data['download_url'] = $disk->temporaryUrl(
                    $report->file_path, 
                    now()->addHours(1)
                );
            } catch (\RuntimeException $e) {
                // Fallback for disks that don't support temporary URLs (like local)
                $data['download_url'] = url('/storage/' . $report->file_path);
            }
        }

        return response()->json(['data' => $data]);
    }
}
