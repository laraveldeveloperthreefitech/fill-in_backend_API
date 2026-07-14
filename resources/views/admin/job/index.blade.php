@extends('admin.layout.app')
@section('content')

<div class="content-wrapper">
  <div class="row">
    <div class="col-sm-12">
      <div class="home-tab">
        <div class="tab-content tab-content-basic">
          <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
            <div class="row">
              <div class="col-lg-12 d-flex flex-column">
                <div class="row flex-grow">
                  <div class="col-12 grid-margin stretch-card">
                    <div class="card card-rounded">
                      <div class="card-body">
                        <h4 class="card-title card-title-dash">Job List</h4>
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <!-- Left-aligned Form -->
                            <form action="" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                <input type="text" name="search" value="{{request()->search}}" class="form-control search-bar w-auto" placeholder="Search...">
                                <input type="hidden" name="id" value="{{request()->id}}">
                                <input type="hidden" name="module" value="{{request()->module}}">
                                <select name="status" class="form-control search-bar w-auto" id="">
                                  <option value="">Select Status</option>
                                  <option value="1" {{request()->status == 1 ? 'selected' : ''}}>Active</option>
                                  <option value="0" {{isset(request()->status) && request()->status == 0 ? 'selected' : ''}}>Inactive</option>
                                </select>
                                <input type="text" name="date" value="{{request()->date}}" class="form-control search-bar w-auto" id="date-range" placeholder="Select Date Range">
                                <button type="submit" class="btn btn-primary search-btn text-white">Filter</button>
                                @if(request()->id)
                                  <a type="button" href="{{route('admin.job',['id' => request()->id,'module' => request()->module])}}" class="btn btn-warning text-white clear-btn">Clear</a>
                                @else
                                  <a type="button" href="{{route('admin.job')}}" class="btn btn-warning text-white clear-btn">Clear</a>
                                @endif
                            </form>

                            <!-- Right-aligned Buttons -->
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <button class="btn btn-success text-white" id="activateAllSelected">Active</button>
                                <button class="btn btn-warning text-white" id="inactivateAllSelected">Inactive</button>
                                {{----------<button class="btn btn-danger">Delete</button>
                                <button class="btn btn-info">Export</button>
                                <button class="btn btn-outline-primary">Import</button>----------}}
                            </div>
                        </div>
                        <div class="table-responsive mt-3">
                          <table class="table select-table">
                            <thead>
                              <tr>
                                <th>
                                  <input type="checkbox" id="check-all">
                                </th>
                                <th>S.No.</th>
                                <th>job Title</th>
                                <th>Clinic Name</th>
                                <th>Department</th>
                                <th>Expiry Date</th>
                                <th>Total Response</th>
                                <th>Status</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody id="indexData">
                              @foreach($data as $index =>$row)
                              <tr>
                                <td><input type="checkbox" name="ids" class="child-checkbox" value="{{ $row->id }}"></td>
                                <td>{{$data->firstItem() + $index}}</td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    
                                    <div>
                                      <h6>{{$row->title}}</h6>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <h6>{{$row->clinic ? $row->clinic->name : ''}}</h6>
                                </td>
                                <td>
                                  <!--<h6>{{$row->department ? $row->department->name : ''}}</h6>-->
                                   <h6>{{ $row->specialization ? $row->specialization->name : '' }}</h6>
                                </td>
                                <!--<td>{{date('d F Y', strtotime($row->expire_date))}}</td>-->
                                 <td>{{ $row->expire_date ? \Carbon\Carbon::parse($row->expire_date)->format('d F Y') : '' }}</td>
                                <td>{{count($row->candidates)}}</td>
                                <td>
                                @if($row->status)
                                <span class="badge bg-success text-white">Active</span>
                                @else
                                <span class="badge bg-warning text-white">Inactive</span>
                                @endif
                                </td>
                                <td>
                                  <div class="d-flex align-items-center">
                                  {{------<a href="javascript:void(0);" class="btn btn-sm btn-primary me-2 editRecruiter" 
                                      data-row="{{ json_encode($row) }}" title="Edit">
                                      <i class="mdi mdi-pencil menu-icon"></i>
                                  </a>------}}

                                  <a href="{{route('view-job',$row->id)}}" class="btn btn-sm btn-warning text-white me-2" 
                                      title="Edit">
                                      <i class="mdi mdi-eye menu-icon"></i>
                                  </a>

                                       
                                        @if($row->status)
                                        <a href="{{ route('job.status', ['id' => $row->id]) }}" class="btn btn-sm btn-success text-white me-2" id="active_link-{{$row->id}}" title="Activate">
                                            <i id="active_ids-{{$row->id}}" class="mdi mdi-toggle-switch menu-icon"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('job.status', ['id' => $row->id]) }}" class="btn btn-sm btn-secondary text-white me-2" id="deactive_link-{{$row->id}}"  title="Activate">
                                            <i id="deactive_ids-{{$row->id}}" class="mdi mdi-toggle-switch-off menu-icon"></i>
                                        </a>
                                        @endif
                                        {{----------<a href="" 
                                                class="btn btn-sm btn-danger me-2 dltBtn" 
                                                title="Delete" 
                                                data-id="{{ $row->id }}">
                                            <i class="mdi mdi-delete menu-icon"></i>
                                        </a>---------}}
                                    </div>
                                </td>
                              </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                         <!-- Bootstrap Pagination -->
                         <div class="float-start mt-3">     
                           Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of {{ $data->total() }} entries
                          </div>
                          <div class="float-end mt-3">
                                        {{ $data->links() }}
                                    </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Recruiter Modal -->
<div class="modal fade" id="editRecruiterModal" tabindex="-1" aria-labelledby="editRecruiterLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content cusomize-recruiter-model">
      <div class="modal-header">
        <h5 class="modal-title" id="editRecruiterLabel">Edit Recruiter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editRecruiterForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="id" id="recruiter_id">
          
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name">
          </div>
          
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email">
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Profile</label>
            <input type="file" class="form-control" accept="image/*" id="profile" name="profile">
          </div>
          <img id="recruiterProfileImage" src="" alt="" style="height:100px; width:100px;">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="editRecruiterLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content cusomize-clinic-model">
      <div class="modal-header">
        <h5 class="modal-title" id="editRecruiterLabel">View Job</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="name" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" disabled>
              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="name" class="form-label">Clinic Name</label>
                <input type="text" class="form-control" id="clinic_name" disabled>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Department</label>
                <input type="email" class="form-control" id="department" disabled>
              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Expiry Date</label>
                <input type="date" class="form-control" id="expiry_date" disabled>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Salary Range From</label>
                <input type="text" class="form-control" id="salry_range_from" disabled>
              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Salary Range To</label>
                <input type="text" class="form-control" id="salry_range_to" disabled>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Experiance Level</label>
                <input type="text" class="form-control" id="experiance_level" disabled>
              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Job Description</label>
                <textarea id="job_description" class="form-control" disabled></textarea>
              </div>
            </div>
          </div>
          <ul class="nav nav-tabs" id="detailsTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="require-tab" data-bs-toggle="tab" data-bs-target="#require" type="button" role="tab">Require Details</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="benefit-tab" data-bs-toggle="tab" data-bs-target="#benefit" type="button" role="tab">Benefits</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="document-tab" data-bs-toggle="tab" data-bs-target="#document" type="button" role="tab">Required Documents</button>
            </li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="require" role="tabpanel">
              <ul id="require_detail_list"></ul>
            </div>
            <div class="tab-pane fade" id="benefit" role="tabpanel">
              <ul id="benefits_list"></ul>
            </div>
            <div class="tab-pane fade" id="document" role="tabpanel">
              <ul id="required_documents_list"></ul>
            </div>
          </div>

          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
     
    </div>
  </div>
</div>
@endsection
@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   var module = 'job';
   var ActivateUrl = "{{ route('job.active') }}";
   var DeActivateUrl = "{{ route('job.de-active') }}";
   var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{asset('admin/assets/js/index.js')}}"></script>
<script>
  $(document).ready(function () {
    // Listen for file input change
    let baseUrl = "{{config('filepaths.job.public_url')}}";

    $(".viewData").on("click", function () {
        let response = $(this).data("row");

        // Format the date if necessary
        let expiryDate = response.expire_date ? new Date(response.expire_date).toISOString().split("T")[0] : "";
        console.log('expiryDate',expiryDate);

        $("#title").val(response.title);
        $("#clinic_name").val(response.clinic.name);
        $("#department").val(response.specialization.name);
        $("#expiry_date").val(expiryDate); // Set formatted date
        $("#salary_range_from").val(response.salary_range_from);
        $("#salary_range_to").val(response.salary_range_to);
        $("#experience_level").val(response.experience_level);
        $("#job_description").val(response.job_description);
       // Populate Require Details
        let requireDetails = response.require_detail ? response.require_detail.split(",") : [];
        $("#require_detail_list").empty();
        requireDetails.forEach(detail => {
            $("#require_detail_list").append(`<li>${detail.trim()}</li>`);
        });

        // Populate Benefits
        let benefitsList = response.benefits ? response.benefits.split(",") : [];
        $("#benefits_list").empty();
        benefitsList.forEach(benefit => {
            $("#benefits_list").append(`<li>${benefit.trim()}</li>`);
        });

        // Populate Required Documents
        let requiredDocuments = response.required_documents ? response.required_documents.split(",") : [];
        $("#required_documents_list").empty();
        requiredDocuments.forEach(document => {
            $("#required_documents_list").append(`<li>${document.trim()}</li>`);
        });

        $("#viewModal").modal("show");
    });



    // Submit Edit Form
    $("#editRecruiterForm").on("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{route('job.update')}}",  // Adjust your update route
            type: "POST",
            data: formData,
            processData: false,  // Prevent jQuery from processing the data
            contentType: false,  // Prevent jQuery from setting content-type
            success: function (response) {
               if(response.status){
                  successMsg("Recruiter Updated successfully!");
                  $("#editRecruiterModal").modal("hide");
                  window.location.reload();

               }else{
                  errorMsg("Something Went Wrong!")
               }
            },
            error: function (xhr, status, error) {
                var response = xhr.responseJSON;
                if (response && response.errors) {
                    $.each(response.errors, function (field, errors) {
                      errorMsg(errors[0]);
                    });
                } else {
                  errorMsg("Something Went Wrong!")
                }
            }

        });
    });

});

</script>
@endpush
