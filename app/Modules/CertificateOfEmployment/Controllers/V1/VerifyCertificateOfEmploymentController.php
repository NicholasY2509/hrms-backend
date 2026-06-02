<?php

namespace App\Modules\CertificateOfEmployment\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment;
use Illuminate\Support\Facades\Storage;

class VerifyCertificateOfEmploymentController extends Controller
{
    public function verify(string $id)
    {
        $coe = CertificateOfEmployment::find($id);

        if (!$coe) {
            return response()->json(['message' => 'Sertifikat tidak ditemukan.'], 404);
        }

        $filePath = $coe->attachment;

        // If no direct attachment, try to find the latest exported report for this COE
        if (!$filePath) {
            $report = \App\Modules\System\Models\Report::where('type', 'certificate_of_employment')
                ->where('status', 'completed')
                ->where('filters->id', $id)
                ->latest()
                ->first();

            if ($report && $report->file_path) {
                $filePath = $report->file_path;
            }
        }

        if (!$filePath) {
            return response()->json(['message' => 'Sertifikat belum memiliki lampiran dokumen.'], 404);
        }

        // Generate a 15-minute temporary signed URL for the private GCS bucket
        $signedUrl = Storage::disk('gcs')->temporaryUrl(
            $filePath,
            now()->addMinutes(15)
        );

        return redirect($signedUrl);
    }
}
