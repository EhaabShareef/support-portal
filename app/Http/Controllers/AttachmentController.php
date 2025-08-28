<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\TicketMessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function download(Request $request, string $uuid)
    {
        // Try to find in standard Attachment model first
        $attachment = Attachment::where('uuid', $uuid)->first();
        
        if ($attachment) {
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

        // Try TicketMessageAttachment model
        $messageAttachment = TicketMessageAttachment::where('uuid', $uuid)->first();
        
        if ($messageAttachment) {
            // Check if user has permission to access this attachment
            if (!$this->canUserAccessTicketMessageAttachment($request->user(), $messageAttachment)) {
                abort(403, 'You do not have permission to access this file.');
            }

            // Check if file exists
            if (!Storage::disk($messageAttachment->disk)->exists($messageAttachment->path)) {
                abort(404, 'File not found.');
            }

            // Determine if this should be viewed in browser or downloaded
            $disposition = $request->query('download', false) ? 'attachment' : 'inline';

            // Get mime type from stored value or detect from file
            $mimeType = $messageAttachment->mime_type 
                ?: Storage::disk($messageAttachment->disk)->mimeType($messageAttachment->path);

            return Storage::disk($messageAttachment->disk)->response(
                $messageAttachment->path,
                $messageAttachment->original_name,
                [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => $disposition . '; filename="' . $messageAttachment->original_name . '"',
                    'Cache-Control' => 'private, max-age=3600',
                ]
            );
        }

        abort(404, 'Attachment not found.');
    }

    public function view(Request $request, string $uuid)
    {
        // Try to find in standard Attachment model first
        $attachment = Attachment::where('uuid', $uuid)->first();
        
        if ($attachment) {
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

        // Try TicketMessageAttachment model
        $messageAttachment = TicketMessageAttachment::where('uuid', $uuid)->first();
        
        if ($messageAttachment) {
            // Check if user has permission to access this attachment
            if (!$this->canUserAccessTicketMessageAttachment($request->user(), $messageAttachment)) {
                abort(403, 'You do not have permission to access this file.');
            }

            // Check if file exists
            if (!Storage::disk($messageAttachment->disk)->exists($messageAttachment->path)) {
                abort(404, 'File not found.');
            }

            // For ticket message attachments, always try to download since we don't have mime type info
            return $this->download($request, $uuid);
        }

        abort(404, 'Attachment not found.');
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

        // Admins can access anything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check based on attachable type
        if ($attachment->attachable_type === 'App\\Models\\Ticket') {
            $ticket = $attachment->attachable;
            
            // Clients can only access attachments from their organization's tickets
            if ($user->hasRole('client')) {
                return $ticket->organization_id === $user->organization_id;
            }

            // Support can access attachments from their department's tickets
            if ($user->hasRole('support') && $user->department) {
                // Check if same department group first
                if ($user->department->department_group_id && 
                    $user->department->department_group_id === $ticket->department?->department_group_id) {
                    return true;
                }
                // Fallback to same department
                return $ticket->department_id === $user->department_id;
            }

            // Admins can access any ticket attachments
            if ($user->hasRole('admin')) {
                return true;
            }
        }

        if ($attachment->attachable_type === 'App\\Models\\TicketMessage') {
            $message = $attachment->attachable;
            $ticket = $message->ticket;
            
            // Same logic as above for ticket messages
            if ($user->hasRole('client')) {
                return $ticket->organization_id === $user->organization_id;
            }

            if ($user->hasRole('support') && $user->department) {
                // Check if same department group first
                if ($user->department->department_group_id && 
                    $user->department->department_group_id === $ticket->department?->department_group_id) {
                    return true;
                }
                // Fallback to same department
                return $ticket->department_id === $user->department_id;
            }

            if ($user->hasRole('admin')) {
                return true;
            }
        }
    }

    private function canUserAccessTicketMessageAttachment($user, TicketMessageAttachment $attachment): bool
    {
        if (!$user) {
            return false;
        }

        // Admins can access anything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Ensure message and ticket relationships exist
        if (!$attachment->message || !$attachment->message->ticket) {
            return false;
        }
        
        $ticket = $attachment->message->ticket;
        
        // Clients can only access attachments from their organization's tickets
        if ($user->hasRole('client')) {
            return $ticket->organization_id === $user->organization_id;
        }

        // Support can access attachments from their department's tickets
        if ($user->hasRole('support') && $user->department) {
            // Check if same department group first
            if ($user->department->department_group_id && 
                $user->department->department_group_id === $ticket->department?->department_group_id) {
                return true;
            }
            // Fallback to same department
            return $ticket->department_id === $user->department_id;
        }

        return false;
    }
    
    private function canUserDeleteAttachment($user, Attachment $attachment): bool
    {
        if (!$user) {
            return false;
        }

        // Admins can delete anything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can delete their own uploads
        return $attachment->uploaded_by === $user->id;
    }
}