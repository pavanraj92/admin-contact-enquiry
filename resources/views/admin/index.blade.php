@extends('admin::admin.layouts.master')

@section('title', 'Enquiry Management')

@section('page-title', 'Enquiry Manager')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Enquiry Manager</li>
@endsection

@section('content')
<!-- Container fluid  -->
<div class="container-fluid">
    <!-- Start Enquiry Content -->
    <div class="row">
        <div class="col-12">
            <div class="card card-body">
                <h4 class="card-title">Filter</h4>
                <form action="{{ route('admin.enquiries.index') }}" method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Keywords</label>
                                <input type="text" name="keyword" id="keyword" class="form-control"
                                    value="{{ app('request')->query('keyword') }}" placeholder="Enter Name, Email Address">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control select2">
                                    <option value="">All</option>
                                    <option value="new" {{ app('request')->query('status') == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="draft" {{ app('request')->query('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="replied" {{ app('request')->query('status') == 'replied' ? 'selected' : '' }}>Replied</option>
                                    <option value="closed" {{ app('request')->query('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-auto mt-1 text-right">
                            <div class="form-group">
                                <label for="created_at">&nbsp;</label>
                                <button type="submit" form="filterForm" class="btn btn-primary mt-4">Filter</button>
                                <a href="{{ route('admin.enquiries.index') }}" class="btn btn-secondary mt-4">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @admincan('enquiries_manager_create')
                    <div class="text-right">
                        <a href="{{ route('admin.enquiries.create') }}" class="btn btn-primary mb-3">Create New Enquiry</a>
                    </div>
                    @endadmincan
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">S. No.</th>
                                    <th scope="col">@sortablelink('name', 'Name', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                    <th scope="col">@sortablelink('email', 'Email', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                    <th scope="col">@sortablelink('message', 'Message', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                    <th scope="col">@sortablelink('status', 'Status', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                    <th scope="col">@sortablelink('created_at', 'Created At', [], ['style' => 'color: #4F5467; text-decoration: none;'])</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($enquiries) && $enquiries->count() > 0)
                                @php
                                $i = ($enquiries->currentpage() - 1) * $enquiries->perpage() + 1;
                                @endphp
                                @foreach ($enquiries as $enquiry)
                                <tr>
                                    <th scope="row">{{ $i }}</th>
                                    <td>{{ $enquiry->name }}</td>
                                    <td>{{ $enquiry->email }}</td>
                                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Message">
                                        {!! $enquiry->message !!}
                                    </td>
                                    <td>
                                        @if ($enquiry->status === 'replied')
                                        <button class="btn btn-secondary status-reply" data-id="{{ $enquiry->id }}">Replied</button>
                                        @elseif ($enquiry->status === 'new')
                                        <button class="btn btn-primary">New</button>
                                        @elseif ($enquiry->status === 'draft')
                                        <button class="btn btn-secondary">Draft</button>
                                        @elseif ($enquiry->status === 'closed')
                                        <button class="btn btn-success">Closed</button>
                                        @else
                                        <span class="badge badge-light">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $enquiry->created_at ? $enquiry->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s') : '—' }}
                                    </td>
                                    <td style="width: 20%;">
                                        @if ($enquiry->status != 'replied' && $enquiry->status != 'closed')
                                        @admincan('enquiry_manager_reply')
                                        <a href="{{ route('admin.enquiries.edit', $enquiry) }}"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="Reply"
                                            class="btn btn-success btn-sm"><i class="mdi mdi-reply"></i></a>
                                        @endadmincan
                                        @endif
                                        @admincan('enquiry_manager_view')
                                        <a href="{{ route('admin.enquiries.show', $enquiry) }}"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="View this record"
                                            class="btn btn-warning btn-sm"><i class="mdi mdi-eye"></i></a>
                                        @endadmincan
                                        @admincan('enquiry_manager_delete')
                                        <a href="javascript:void(0)"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="Delete this record"
                                            data-url="{{ route('admin.enquiries.destroy', $enquiry) }}"
                                            data-text="Are you sure you want to delete this record?"
                                            data-method="DELETE"
                                            class="btn btn-danger btn-sm delete-record"><i class="mdi mdi-delete"></i></a>
                                        @endadmincan

                                    </td>
                                </tr>
                                @php
                                $i++;
                                @endphp
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-center">No records found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                        <!--pagination move the right side-->
                        @if ($enquiries->count() > 0)
                        {{ $enquiries->links('admin::pagination.custom-admin-pagination') }}
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End enquiry Content -->
</div>
<!-- End Container fluid  -->
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.status-reply').on('click', function() {
            const button = $(this); // cache the reference
            const enquiryId = button.data('id');

            if (!enquiryId || button.text().trim() !== 'Replied') return;

            fetch(`/admin/enquiries/${enquiryId}/close-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        button.replaceWith(data.label); // Replace the button

                        // ✅ Show success message
                        Swal.fire('Success', data.message, 'success'); // OR alert(data.message);
                    } else {
                        alert(data.message || 'Failed to update status.');
                    }
                })
                .catch(() => {
                    alert('An error occurred while updating status.');
                });
        });
    });
</script>


@endpush