@extends('admin.layout.app')
@section('content')

<style>
    .subject-column {
        white-space: normal;
        word-wrap: break-word;
        max-width: 400px;
    }
    .table td, .table th {
        vertical-align: middle !important;
    }
    .modal-header h5 {
        margin: 0;
    }
    .search-form {
        display: flex;
        gap: 10px;
        align-items: center;
    }
</style>

<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">Clinic Support</h3>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Contact List</h4>
                        <form class="search-form" action="{{ route('clinic-support.index') }}" method="GET">
                            <input class="form-control" type="text" name="search" placeholder="Search by Title" value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <!-- <th class="subject-column">Subject</th> -->
                                    <th class="subject-column">Message</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($data) > 0)
                                    @foreach($data as $key => $row)
                                        <tr>
                                            <td>{{ $data->firstItem() + $key }}</td>
                                            <td>{{ $row->clinic->name }}</td>
                                            <!-- <td class="subject-column">{{ $row->subject }}</td> -->
                                            <td class="subject-column">{{ $row->message }}</td>
                                            <td>
                                                @if(!$row->response_status)
                                                    <button class="btn btn-sm btn-danger respond-btn"
                                                        data-id="{{ $row->id }}"
                                                        data-email="{{ $row->email }}">
                                                        Respond
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-success" disabled>Responded</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">No Record Found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <p class="mb-0">Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of {{ $data->total() }} entries</p>
                        {{ $data->links('pagination::bootstrap-4') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <form action="{{ route('clinic-support.respond') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">Send Response to User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
           
                <input type="hidden" id="support_id" name="support_id"> 
                <input type="hidden" id="user_email" name="user_email">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="message">Reply</label>
                        <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Send</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
    $(document).ready(function () {
        $('.respond-btn').click(function () {
            const contactId = $(this).data('id');
            const email = $(this).data('email');

            $('#support_id').val(contactId); 
            $('#user_email').val(email);

            $('#responseModal').modal('show');
        });
    });
</script>
@endpush
