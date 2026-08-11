<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\VisitPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitFileController extends Controller
{
    public function photo(Request $request, VisitPhoto $photo): StreamedResponse
    {
        $this->authorizeVisit($request, $photo->visit);

        return Storage::disk('visits')->response($photo->disk_path);
    }

    public function signature(Request $request, Visit $visit, string $who): StreamedResponse
    {
        $this->authorizeVisit($request, $visit);

        $path = match ($who) {
            'worker' => $visit->worker_signature_path,
            'second' => $visit->second_signer_path,
            default => abort(404),
        };

        abort_if(blank($path), 404);

        return Storage::disk('visits')->response($path);
    }

    private function authorizeVisit(Request $request, Visit $visit): void
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_unless($user->isAdmin() || $visit->worker_id === $user->id, 403);
    }
}
