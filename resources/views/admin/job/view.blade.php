@extends('admin.layout.app')

@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-rounded shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4 border-bottom pb-2">Job Details</h3>

                    <div class="row mb-4">
                       
                        
                        <div class="col-md-4 text-center">
    @if($data->clinic && $data->clinic->profile)
        <img 
            src="{{ config('filepaths.recruiter.public_url') . $data->clinic->profile }}" 
            class="img-thumbnail rounded" 
            width="150"
        >
    @else
        <div class="text-muted">No Profile</div>
    @endif
</div>


                        <div class="col-md-8">
                            <h4>{{ $data->title ?? 'N/A' }}</h4>
                            <p><strong>Clinic:</strong> {{ $data->clinic ?  $data->clinic->name :  'N/A' }}</p>
                            <p><strong>Address:</strong> {{ $data->address ?? 'N/A' }}</p>
                            <p><strong>Short Address:</strong> {{ $data->short_address ?? 'N/A' }}</p>
                            <p><strong>Vacancy:</strong> {{ $data->vacancy ?? 'N/A' }}</p>
                            <p><strong>Expire Date:</strong> {{ $data->expire_date ? \Carbon\Carbon::parse($data->expire_date)->format('d M Y') : 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary">Job Description</h5>
                    <p>{{ $data->job_description ?? 'N/A' }}</p>

                    <hr>

                    <h5 class="text-primary">Job Preferences</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Profession:</strong> {{ $data->specialization ? $data->specialization->name :  'N/A' }}</p>
                            <p><strong>Experience Level:</strong> {{ $data->experiance_level ?? 'N/A' }}</p>
                            <p><strong>Salary Range:</strong> ${{ $data->salary_range_from ?? 'N/A' }} - ₹{{ $data->salary_range_to ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Shifts:</strong> 
                                {{ $data->shift}}
                            </p>
                            <p><strong>Benefits:</strong> 
                                {{  $data->benefits}}
                            </p>
                            <p><strong>Required Software:</strong> 
                                @if(!empty($data->softwareList))
                                    {{-- Replace this with actual names if software IDs map to a table --}}
                                    {{ implode(', ', $data->softwareList()->pluck('name')->toArray()) }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary">Candidates Summary</h5>
                    <p><strong>Number of Candidates Applied:</strong> {{ $data->candidates_count ?? 0 }}</p>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
