@extends('admin.layout.app')

@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-rounded">
                <div class="card-body">
                    <h4 class="card-title">Edit Recruiter</h4>

                    <form action="{{ route('recruiter.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{$data->id}}">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $data->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $data->email }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="profile" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile" name="profile" accept="image/*">
                            @if($data->profile)
                                <img src="{{ config('filepaths.recruiter.public_url') . $data->profile }}" alt="Profile Picture" class="mt-2 rounded-circle" width="100">
                            @endif
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary text-white">Save Changes</button>
                            <a href="{{ route('admin.candidate') }}" class="btn btn-secondary text-white">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
