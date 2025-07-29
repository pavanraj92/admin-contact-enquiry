<?php

namespace admin\enquiries\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use admin\enquiries\Requests\UpdateEnquiryRequest;
use admin\enquiries\Models\Enquiry;
use admin\enquiries\Emails\EnquiryReplyByAdminMail;

class EnquiryManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admincan_permission:enquiry_manager_list')->only(['index']);
        $this->middleware('admincan_permission:enquiry_manager_reply')->only(['edit', 'update']);
        $this->middleware('admincan_permission:enquiry_manager_view')->only(['show']);
        $this->middleware('admincan_permission:enquiry_manager_delete')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $enquiries = Enquiry::filter($request->query('keyword'))
                ->filterByStatus($request->query('status'))
                ->sortable()
                ->latest()
                ->paginate(Enquiry::getPerPageLimit())
                ->withQueryString();

            return view('enquiries::admin.index', compact('enquiries'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load enquiries: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show(Enquiry $enquiry)
    {
        try {
            return view('enquiries::admin.show', compact('enquiry'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load enquiry: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enquiry $enquiry)
    {
        try {
            return view('enquiries::admin.createOrEdit', compact('enquiry'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load enquiry for editing: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnquiryRequest $request, Enquiry $enquiry)
    {
        try {
            $requestData = $request->validated();
            $action = $request->input('action');

            // Common updates
            $enquiry->admin_reply = $requestData['admin_reply'];
            $enquiry->replied_by = auth('admin')->id();

            // Status logic
            if ($action === 'reply') {
                $enquiry->status = 'replied';
                $enquiry->is_replied = true;
                $enquiry->replied_at = now();
            } elseif ($action === 'draft') {
                $enquiry->status = 'draft';
            }

            $enquiry->save();

            // Load admin relation (assuming it's repliedBy)
            $enquiry->load('repliedBy');

            // Only send mail on reply
            if ($action === 'reply') {
                $msg = 'Reply sent successfully.';
                Mail::to($enquiry->email)->send(new EnquiryReplyByAdminMail($enquiry));
            } else {
                $msg = 'Draft saved successfully.';
            }
            return redirect()->route('admin.enquiries.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update enquiry: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enquiry $enquiry)
    {
        try {
            $enquiry->delete();
            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete record.', 'error' => $e->getMessage()], 500);
        }
    }

    public function closeStatus(Request $request, Enquiry $enquiry)
    {
        // Close enquiry status
        if ($enquiry->status === 'replied') {
            $enquiry->status = 'closed';
            $enquiry->save();

            return response()->json([
                'success' => true,
                'new_status' => 'closed',
                'label' => '<button class="btn btn-success status-badge" data-id="' . $enquiry->id . '">Closed</button>',
                'message' => 'Enquiry closed successfully'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Status not allowed to change.'], 403);
    }
}
