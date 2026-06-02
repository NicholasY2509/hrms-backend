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

        if (!$coe || !$coe->attachment) {
            return response()->json(['message' => 'Sertifikat tidak ditemukan atau belum memiliki lampiran dokumen.'], 404);
        }

        // Generate a 15-minute temporary signed URL for the private GCS bucket
        $signedUrl = Storage::disk('gcs')->temporaryUrl(
            $coe->attachment,
            now()->addMinutes(15)
        );

        return redirect($signedUrl);
    }
}
