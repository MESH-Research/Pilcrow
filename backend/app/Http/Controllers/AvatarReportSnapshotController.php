<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AvatarReport;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AvatarReportSnapshotController extends Controller
{
    /**
     * Stream the retained private snapshot of a reported avatar to a moderator.
     *
     * The image lives on the non-public media_private disk and is never
     * publicly addressable; access is gated by the moderateAvatars ability,
     * re-checked here on every request.
     */
    public function show(AvatarReport $avatarReport): StreamedResponse|Response
    {
        $this->authorize('moderateAvatars');

        $media = $avatarReport->getSnapshotMedia();
        if ($media === null) {
            abort(404);
        }

        return $media->toInlineResponse(request());
    }
}
