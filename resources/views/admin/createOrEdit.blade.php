@extends('admin::admin.layouts.master')

@section('title', 'Enquiry Management')

@section('page-title', 'Reply Enquiry')

@push('styles')
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.0.0/ckeditor5.css">
<!-- Custom CSS for the page -->
<link rel="stylesheet" href="{{ asset('backend/custom.css') }}">
@endpush

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.enquiries.index') }}">Enquiry Manager</a></li>
<li class="breadcrumb-item active" aria-current="page">Reply Enquiry</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Start Enquiry Content -->
    <div class="row">
        <div class="col-12">
            <div class="card card-body">
                <form action="{{route('admin.enquiries.update', $enquiry->id)}}"
                    method="POST" id="enquiryForm" enctype="multipart/form-data">
                    @if (isset($enquiry))
                    @method('PUT')
                    @endif
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ $enquiry?->name ?? old('name') }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" class="form-control"
                                    value="{{ $enquiry?->email ?? old('email') }}" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Message</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ $enquiry?->message ?? old('message') }}" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Admin Reply<span class="text-danger">*</span></label>
                        <textarea name="admin_reply" id="admin_reply" class="form-control description-editor">{{ $enquiry?->admin_reply ?? old('admin_reply') }}</textarea>
                        @error('admin_reply')
                        <div class="text-danger validation-error">{{ $admin_reply }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-danger" name="action" value="draft" id="draftBtn">Save as Draft</button>
                        <button type="submit" class="btn btn-primary" name="action" value="reply" id="saveBtn">Send Reply</button>
                        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End enquiry Content -->
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
<script>
    let ckEditorInstance;
    let clickedButton = null;

    ClassicEditor
        .create(document.querySelector('#admin_reply'))
        .then(editor => {
            ckEditorInstance = editor;
            editor.ui.view.editable.element.style.minHeight = '250px';
            editor.ui.view.editable.element.style.maxHeight = '250px';
            editor.ui.view.editable.element.style.overflowY = 'auto';

            // Trigger validation on typing
            editor.model.document.on('change:data', () => {
                const adminReplyVal = editor.getData();
                $('#admin_reply').val(adminReplyVal);
                $('#admin_reply').trigger('keyup');
            });
        })
        .catch(error => console.error(error));

    $(function() {
        // Detect which button is clicked
        $('#saveBtn, #draftBtn').on('click', function() {
            clickedButton = this;
        });

        $('#enquiryForm').validate({
            ignore: [],
            rules: {
                admin_reply: {
                    required: true,
                    minlength: 3
                }
            },
            messages: {
                admin_reply: {
                    required: "Please enter your reply",
                    minlength: "Your reply must be at least 3 characters long"
                }
            },
            submitHandler: function(form) {
                if (clickedButton) {
                    const isSaveBtn = $(clickedButton).is('#saveBtn');
                    $(clickedButton).prop('disabled', true).text(
                        isSaveBtn ? 'Sending reply...' : 'Saving as draft...'
                    );
                }
                form.submit();
            },
            errorElement: 'div',
            errorClass: 'text-danger custom-error',
            errorPlacement: function(error, element) {
                $('.validation-error').hide(); // hide Blade errors
                if (element.attr("id") === "admin_reply") {
                    error.insertAfter($('.ck-editor')); // show below CKEditor UI
                } else {
                    error.insertAfter(element);
                }
            },
            success: function() {
                $('.validation-error').hide();
            }
        });
    });
</script>
@endpush