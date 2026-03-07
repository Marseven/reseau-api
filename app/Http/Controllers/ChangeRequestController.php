<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Models\Coffret;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreChangeRequestRequest;
use App\Http\Requests\ReviewChangeRequestRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ChangeRequest::with(['coffret', 'requester', 'reviewer']);

        // Non-admin users only see their own requests
        if (!$request->user()->hasRole('administrator')) {
            $query->where('requester_id', $request->user()->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('coffret_id')) {
            $query->where('coffret_id', $request->coffret_id);
        }

        if ($request->has('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $changeRequests = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 15));

        return ApiResponse::success($changeRequests);
    }

    public function store(StoreChangeRequestRequest $request)
    {
        $coffret = Coffret::with('equipments.ports')->findOrFail($request->coffret_id);

        $data = $request->validated();
        $data['requester_id'] = $request->user()->id;
        $data['status'] = 'en_attente';
        $data['snapshot_before'] = $coffret->toArray();

        // Handle photo uploads
        if ($request->hasFile('photo_before')) {
            $data['photo_before'] = $request->file('photo_before')->store('change-requests/photos', 'public');
        }

        if ($request->hasFile('photo_after')) {
            $data['photo_after'] = $request->file('photo_after')->store('change-requests/photos', 'public');
        }

        // Remove file objects from data before create
        unset($data['photo_before_file'], $data['photo_after_file']);

        $changeRequest = ChangeRequest::create($data);

        app(NotificationService::class)->notifyNewChangeRequest($changeRequest->load('coffret'));

        return ApiResponse::created($changeRequest->load('coffret', 'requester'), 'Demande de modification créée avec succès.');
    }

    public function show(ChangeRequest $changeRequest)
    {
        return ApiResponse::success(
            $changeRequest->load('coffret.equipments', 'requester', 'reviewer')
        );
    }

    public function review(ReviewChangeRequestRequest $request, ChangeRequest $changeRequest)
    {
        if (!in_array($changeRequest->status, ['en_attente', 'en_revision'])) {
            return ApiResponse::error('Cette demande a déjà été traitée.', 422);
        }

        $changeRequest->update([
            'status' => $request->status,
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),
            'review_comment' => $request->review_comment,
        ]);

        // Auto-apply if approved and type is changement_statut
        if ($request->status === 'approuvee' && $changeRequest->type === 'changement_statut') {
            $coffret = $changeRequest->coffret;
            $newStatus = $coffret->status === 'active' ? 'inactive' : 'active';
            $coffret->update(['status' => $newStatus]);

            $changeRequest->update([
                'snapshot_after' => $coffret->fresh()->load('equipments.ports')->toArray(),
            ]);
        }

        app(NotificationService::class)->notifyChangeRequestReviewed($changeRequest);

        return ApiResponse::success(
            $changeRequest->load('coffret', 'requester', 'reviewer'),
            'Demande de modification mise à jour avec succès.'
        );
    }

    public function destroy(ChangeRequest $changeRequest)
    {
        if ($changeRequest->status !== 'en_attente') {
            return ApiResponse::error('Seules les demandes en attente peuvent être supprimées.', 422);
        }

        $user = auth()->user();
        if ($changeRequest->requester_id !== $user->id && !$user->hasRole('administrator')) {
            return ApiResponse::forbidden('Vous ne pouvez supprimer que vos propres demandes.');
        }

        $changeRequest->delete();

        return ApiResponse::success(null, 'Demande de modification supprimée avec succès.');
    }
}
