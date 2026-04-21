<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Models\SavedRequestForm;
use App\Models\FacilityRequest;
use App\Models\WorkOrder;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormTemplateController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        return $request->user()->role->name === 'Admin';
    }

    private function isAdminOrStaff(Request $request): bool
    {
        return in_array($request->user()->role->name, ['Admin', 'Staff']);
    }

    public function index(Request $request)
    {
        $templates = FormTemplate::with('creator')->where('is_active', true)->latest()->get();

        return response()->json(['success' => true, 'data' => $templates]);
    }

    public function store(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'form_type' => 'required|in:facility_request,work_order,general',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('form-templates', 'local');

        $template = FormTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'form_type' => $request->form_type,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::log('created', 'Admin uploaded form template: ' . $template->name, $template);

        return response()->json(['success' => true, 'data' => $template->load('creator'), 'message' => 'Template uploaded'], 201);
    }

    public function update(Request $request, $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $template = FormTemplate::findOrFail($id);
        $oldValues = $template->toArray();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'form_type' => 'sometimes|in:facility_request,work_order,general',
            'is_active' => 'sometimes|boolean',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = $request->only(['name', 'description', 'form_type', 'is_active']);

        if ($request->hasFile('file')) {
            Storage::disk('local')->delete($template->file_path);
            $file = $request->file('file');
            $data['file_path'] = $file->store('form-templates', 'local');
            $data['file_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
        }

        $template->update($data);

        ActivityLog::log('updated', 'Admin updated form template: ' . $template->name, $template, $oldValues, $template->toArray());

        return response()->json(['success' => true, 'data' => $template->load('creator'), 'message' => 'Template updated']);
    }

    public function destroy(Request $request, $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $template = FormTemplate::findOrFail($id);
        Storage::disk('local')->delete($template->file_path);

        ActivityLog::log('deleted', 'Admin deleted form template: ' . $template->name, $template);
        $template->delete();

        return response()->json(['success' => true, 'message' => 'Template deleted']);
    }

    public function download(Request $request, $id)
    {
        $template = FormTemplate::findOrFail($id);

        if (!Storage::disk('local')->exists($template->file_path)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        return Storage::disk('local')->download($template->file_path, $template->file_name, [
            'Content-Type' => $template->mime_type,
        ]);
    }

    public function savedForms(Request $request)
    {
        if (!$this->isAdminOrStaff($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $saved = SavedRequestForm::with('savedBy')->latest()->get()->map(function ($record) {
            $record->request_data = $record->request;
            return $record;
        });

        return response()->json(['success' => true, 'data' => $saved]);
    }

    public function saveRequest(Request $request)
    {
        if (!$this->isAdminOrStaff($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'request_type' => 'required|in:facility_request,work_order',
            'request_id' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $saved = SavedRequestForm::updateOrCreate(
            ['request_type' => $request->request_type, 'request_id' => $request->request_id],
            ['saved_by' => $request->user()->id, 'notes' => $request->notes]
        );

        ActivityLog::log('created', "Saved {$request->request_type} #{$request->request_id} to form records", $saved);

        return response()->json(['success' => true, 'data' => $saved, 'message' => 'Request saved']);
    }

    public function printRequest(Request $request, $type, $id)
    {
        if (!$this->isAdminOrStaff($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($type === 'facility_request') {
            $record = FacilityRequest::with(['user', 'department', 'approvalSteps'])->findOrFail($id);
            $equipment = array_filter([
                'Chair' => $record->chair,
                'Podium' => $record->podium,
                'Tent' => $record->tent,
                'Tables' => $record->tables,
                'Booths' => $record->booths,
                'Sound System' => $record->sound_system,
                'Extension' => $record->extension,
                'Microphones' => $record->microphones,
                'Skirting' => $record->skirting,
                'Flags' => $record->flags,
                'Others' => $record->others,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'Facility Request',
                    'id' => $record->id,
                    'requestor' => $record->user->name ?? 'N/A',
                    'department' => $record->department->name ?? 'N/A',
                    'venue' => $record->venue_requested,
                    'location' => $record->location_room_number,
                    'event_title' => $record->title_of_event,
                    'date' => $record->date_of_event?->format('F d, Y'),
                    'time' => $record->time_of_event,
                    'equipment' => array_keys($equipment),
                    'others_description' => $record->others_description,
                    'status' => $record->status,
                    'submitted_at' => $record->created_at->format('F d, Y h:i A'),
                ]
            ]);
        }

        if ($type === 'work_order') {
            $record = WorkOrder::with(['user', 'department', 'assignee', 'approvalSteps'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'Work Order',
                    'id' => $record->id,
                    'requestor' => $record->user->name ?? 'N/A',
                    'requisitioner' => $record->requisitioner,
                    'department' => $record->department->name ?? 'N/A',
                    'location' => $record->location,
                    'room_number' => $record->room_number,
                    'date' => $record->date?->format('F d, Y'),
                    'time' => $record->time,
                    'description' => $record->description_of_problem,
                    'priority' => $record->priority,
                    'status' => $record->status,
                    'assigned_to' => $record->assignee->name ?? 'Unassigned',
                    'submitted_at' => $record->created_at->format('F d, Y h:i A'),
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request type'], 422);
    }
}
