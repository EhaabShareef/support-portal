<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function download(Request $request, string $uuid)
    {
        $attachment = Attachment::where('uuid', $uuid)->firstOrFail();

        // Check if user has permission to access this attachment
        if (!$this->canUserAccessAttachment($request->user(), $attachment)) {
            abort(403, 'You do not have permission to access this file.');
        }

        // Check if file exists
        if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'File not found.');
        }

        // Increment download count
        $attachment->incrementDownloadCount();

        // Determine if this should be viewed in browser or downloaded
        $disposition = $request->query('download', false) ? 'attachment' : 'inline';

        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => $disposition . '; filename="' . $attachment->original_name . '"',
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }

    public function view(Request $request, string $uuid)
    {
        $attachment = Attachment::where('uuid', $uuid)->firstOrFail();

        // Check if user has permission to access this attachment
        if (!$this->canUserAccessAttachment($request->user(), $attachment)) {
            abort(403, 'You do not have permission to access this file.');
        }

        // Check if file exists
        if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'File not found.');
        }

        // Only allow viewing of certain file types in browser
        if (!$attachment->canBeViewedInBrowser()) {
            return $this->download($request, $uuid);
        }

        // Return file for inline viewing
        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline; filename="' . $attachment->original_name . '"',
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }

    public function destroy(Request $request, string $uuid)
    {
        $attachment = Attachment::where('uuid', $uuid)->firstOrFail();

        // Check if user has permission to delete this attachment
        if (!$this->canUserDeleteAttachment($request->user(), $attachment)) {
            abort(403, 'You do not have permission to delete this file.');
        }

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully.']);
    }

    private function canUserAccessAttachment($user, Attachment $attachment): bool
    {
        if (!$user) {
            return false;
        }

        // Super admins can access anything
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Check based on attachable type
        if ($attachment->attachable_type === 'App\\Models\\Ticket') {
            $ticket = $attachment->attachable;
            
            // Clients can only access attachments from their organization's tickets
            if ($user->hasRole('Client')) {
                return $ticket->organization_id === $user->organization_id;
            }

            // Agents can access attachments from their department's tickets
            if ($user->hasRole('Agent')) {
                return $ticket->department_id === $user->department_id;
            }

            // Admins can access any ticket attachments
            if ($user->hasRole('Admin')) {
                return true;
            }
        }

        if ($attachment->attachable_type === 'App\\Models\\TicketMessage') {
            $message = $attachment->attachable;
            $ticket = $message->ticket;
            
            // Same logic as above for ticket messages
            if ($user->hasRole('Client')) {
                return $ticket->organization_id === $user->organization_id;
            }

            if ($user->hasRole('Agent')) {
                return $ticket->department_id === $user->department_id;
            }

            if ($user->hasRole('Admin')) {
                return true;
            }
        }

        // Default: only the uploader can access
        return $attachment->uploaded_by === $user->id;
    }

    private function canUserDeleteAttachment($user, Attachment $attachment): bool
    {
        if (!$user) {
            return false;
        }

        // Super admins and admins can delete anything
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        // Users can delete their own uploads
        return $attachment->uploaded_by === $user->id;
    }
}