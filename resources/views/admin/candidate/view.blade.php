@extends('admin.layout.app')

@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-rounded shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4 border-bottom pb-2">Candidate Profile</h3>

                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            @if($data->profile)
                                <img src="{{config('filepaths.candidate.public_url') . $data->profile}}" class="rounded-circle img-thumbnail" width="150">
                            @else
                                <div class="text-muted">No Profile Picture</div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4>{{ $data->name ?? 'N/A' }}</h4>
                            <p><strong>Email:</strong> {{ $data->email ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $data->phone ?? 'N/A' }}</p>
                            <p><strong>Location:</strong> {{ $data->address ?? 'N/A' }}</p>
                            <p><strong>Profession:</strong> {{ $data->specialization_name ?? 'N/A',  }}</p>
                            <p><strong>Rating:</strong> <span class="badge bg-success">{{ count($data->review) > 0 ? number_format($data->review->sum('rate')/count($data->review),1) : 0, }}</span></p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary">Experience & Skills</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Experience Type:</strong> {{ $data->type_of_experiance ?? 'N/A' }}</p>
                            <p><strong>Years of Experience:</strong> {{ $data->year_of_experiance ?? 'N/A' }}</p>
                            <p><strong>Hourly Rate:</strong> {{ $data->hourly_rate ?? 'N/A' }}</p>
                            <p><strong>Radius:</strong> {{ $data->radius ?? 'N/A' }}</p>
                            <p><strong>Availability:</strong> {{ $data->availability_time ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Software Experience:</strong> {{ $data->software_experiance ? implode(', ', $data->software_experiance->pluck('name')->toArray()) : 'N/A' }}</p>
                            <p><strong>Other Software:</strong> {{ $data->other_software ?? 'N/A' }}</p>
                            <p><strong>Qualifications:</strong> {{ $data->qualification ? implode(', ', $data->qualification->pluck('name')->toArray()) : 'N/A' }}</p>
                            <p><strong>Other Qualifications:</strong> {{ $data->other_qualification ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary">Additional Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Languages:</strong> {{ $data->language ? implode(', ', $data->language->toArray()) : 'N/A' }}</p>
                            <p><strong>Fun Fact:</strong> {{ $data->fun_fact ?? 'N/A' }}</p>
                            <p><strong>Environment Thrive In:</strong></p>
                            @if(!empty(json_decode($data->environment_thrive)))
                                <ul class="list-unstyled">
                                    @foreach(json_decode($data->environment_thrive) as $item)
                                        <li>• {{ $item }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>N/A</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p><strong>Vaccinations:</strong> {{ $data->vaccination ? implode(', ', $data->vaccination->pluck('name')->toArray()) : 'N/A' }}</p>
                            <p><strong>Other Vaccination:</strong> {{ $data->other_vaccination ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary">Checks & Preferences</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Short Notice:</strong> {{ $data->short_notice ? 'Yes' : 'No' }}</p>
                            <p><strong>Permanent Opportunities:</strong> {{ $data->permanent_opportunities ? 'Yes' : 'No' }}</p>
                            <p><strong>Children's Check:</strong> {{ $data->childrens_check ? 'Yes' : 'No' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Police Check:</strong> {{ $data->valid_police_check ? 'Yes' : 'No' }}</p>
                            <p><strong>First Aid Certificate:</strong> {{ $data->first_aid_certicate ? 'Yes' : 'No' }}</p>
                            <p><strong>Working in Dentistry:</strong> {{ $data->working_in_dentistry ? 'Yes' : 'No' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary">Documents</h5>
                    <p>
                        @if($data->document)
                            <a href="{{ config('filepaths.candidate.public_url') . $data->document }}" target="_blank" class="btn btn-outline-primary btn-sm">View Document</a>
                        @else
                            <span class="text-muted">No document uploaded</span>
                        @endif
                    </p>

                    @if(!empty($data->review))
                        <hr>
                        <h5 class="text-primary">Reviews</h5>
                        @foreach($data->review as $review)
                            <div class="card mb-3 p-3 shadow-sm">
                                <p><strong>Recruiter:</strong> {{ $review->clinic ? $review->clinic->name : 'N/A' }}</p>
                                <p><strong>Rating:</strong> <span class="badge bg-warning text-dark">{{ $review['rate'] }}</span></p>
                                <p><strong>Comment:</strong> {{ $review['comment'] }}</p>
                                @if($review['image'])
                                    <img src="{{ $review['image'] }}" width="80" class="img-thumbnail">
                                @endif
                                <p class="text-muted mb-0"><strong>Date:</strong> {{ \Carbon\Carbon::parse($review['created'])->format('d M Y') }}</p>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No reviews found.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
