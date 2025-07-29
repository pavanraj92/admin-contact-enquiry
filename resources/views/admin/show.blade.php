@extends('admin::admin.layouts.master')

@section('title', 'Enquiry Management')

@section('page-title', 'Enquiry Details')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.enquiries.index') }}">Enquiry Manager</a></li>
<li class="breadcrumb-item active" aria-current="page">Enquiry Details</li>
@endsection

@section('content')
<!-- Container fluid  -->
<div class="container-fluid">
    <!-- Start Enquiry Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <div class="card-body">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th scope="row">Name</th>
                                    <td scope="col">{{ $enquiry->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Email</th>
                                    <td scope="col">{{ $enquiry->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Message</th>
                                    <td scope="col">{!! $enquiry->message ?? 'N/A' !!}</td>
                                </tr>

                                <tr>
                                    <th scope="row">Admin Reply</th>
                                    <td scope="col">{!! $enquiry->admin_reply ?? 'N/A' !!}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Status</th>
                                    <td scope="col">{{ ucfirst($enquiry->status) ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Is Replied</th>
                                    <td scope="col">
                                        {{ $enquiry->is_replied ? 'Yes' : 'No' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Replied At</th>
                                    <td scope="col">
                                        {{ $enquiry->replied_at
                                            ? $enquiry->replied_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s')
                                            : '—' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Replied By</th>
                                    <td scope="col">{{ $enquiry->repliedBy->full_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Created At</th>
                                    <td scope="col">
                                        {{ $enquiry->created_at
                                            ? $enquiry->created_at->format(config('GET.admin_date_time_format') ?? 'Y-m-d H:i:s')
                                            : '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End enquiry Content -->
</div>
<!-- End Container fluid  -->
@endsection