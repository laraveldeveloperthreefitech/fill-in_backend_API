@extends('admin.layout.app')

@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-rounded shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4 border-bottom pb-2">Dental Practice Profile</h3>

                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            @if($data->profile)
                                <img src="{{ $data->profile }}" class="rounded-circle img-thumbnail" width="150">
                            @else
                                <div class="text-muted">No Profile Picture</div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4>{{ $data->name ?? 'N/A' }}</h4>
                            <p><strong>Email:</strong> {{ $data->email ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $data->phone ?? 'N/A' }}</p>
                            <p><strong>Practice Name:</strong> {{ $data->clinic ? $data->clinic->name : 'N/A' }}</p>
                            <p><strong>Established Year:</strong> {{$data->clinic ?  $data->clinic->established_year : 'N/A' }}</p>
                            <p><strong>Practice Size:</strong> {{ $data->clinic->practice_size ?? 'N/A' }}</p>
                            <p><strong>Primarily Looking For:</strong> {{ $data->clinic->primarly_looking ?? 'N/A' }}</p>
                            <p><strong>Working Hours:</strong> {{ $data->clinic ? $data->clinic->working_hours : 'N/A' }}</p>
                            <p><strong>Address:</strong> {{ $data->clinic ? $data->clinic->address :  'N/A' }}</p>
                            <p><strong>Phone Verified:</strong> {{ $data->phone_verified ? 'Yes' : 'No' }}</p>
                            <p><strong>Created At:</strong> {{ $data->created_at ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary">Software & Role Information</h5>
                    <p><strong>Use Software:</strong> {{ count($data->useSoftware) > 0? implode(', ', $data->useSoftware()->pluck('name')->toArray()) : 'N/A' }}</p>
                    <p><strong>Other Software:</strong> {{ $data->other_software ?? 'N/A' }}</p>
                    <p><strong>Practice Role:</strong> {{ $data->RoleInPractice ? $data->RoleInPractice->name : 'N/A' }}</p>
                    <p><strong>Other Practice Role:</strong> {{ $data->other_practice_role ?? 'N/A' }}</p>

                    <hr>

                    <h5 class="text-primary">Looking & Dentistry Preferences</h5>
                    <p><strong>Looking:</strong> {{ count($data->lookingFor) > 0 ? implode(', ', $data->lookingFor()->pluck('name')->toArray()) : 'N/A' }}</p>
                    <p><strong>Dentistry:</strong> {{ count($data->dentistryPractices) > 0 ? implode(', ', $data->dentistryPractices->pluck('name')->toArray()) : 'N/A' }}</p>
                    <p><strong>Other Dentistry:</strong> {{ $data->other_dentistry ?? 'N/A' }}</p>

                    <hr>

                    <h5 class="text-primary">Documents</h5>
                    <p>
                        @if($data->document)
                            <a href="{{ $data->document }}" target="_blank" class="btn btn-outline-primary btn-sm">View Document</a>
                        @else
                            <span class="text-muted">No document uploaded</span>
                        @endif
                    </p>
                    <p><strong>Document Name:</strong> {{ $data->document_name ?? 'N/A' }}</p>

                    @if(count($data->review) > 0)
                        <hr>
                        <h5 class="text-primary">Reviews ({{ $data->review_count }})</h5>
                        @foreach($data->review as $review)
                            <div class="card mb-3 p-3 shadow-sm">
                                <p><strong>Candidate:</strong> {{ $review->candidate ? $review->candidate->name : 'N/A' }}</p>
                                <p><strong>Rating:</strong> <span class="badge bg-warning text-dark">{{ $review->rate }}</span></p>
                                <p><strong>Comment:</strong> {{ $review->comment ?? 'N/A' }}</p>
                                @if($review->candidate && $review->candidate->profile)
                                    <img src="{{config('filepaths.candidate.public_url') .$review->candidate->profile}}" width="80" class="img-thumbnail">
                                @endif
                                <p class="text-muted mb-0"><strong>Date:</strong> {{ \Carbon\Carbon::parse($review['created'])->format('d M Y') }}</p>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No reviews found.</p>
                    @endif

                    <hr>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
