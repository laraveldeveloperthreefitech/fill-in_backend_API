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
                        <h4 class="card-title card-title-dash">Clinic List</h4>
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <!-- Left-aligned Form -->
                            <form action="" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                <input type="text" name="search" value="{{request()->search}}" class="form-control search-bar w-auto" placeholder="Search...">
                                <select name="status" class="form-control search-bar w-auto" id="">
                                  <option value="">Select Status</option>
                                  <option value="1" {{request()->status == 1 ? 'selected' : ''}}>Active</option>
                                  <option value="0" {{isset(request()->status) && request()->status == 0 ? 'selected' : ''}}>De-Active</option>
                                </select>
                                <input type="text" name="date" value="{{request()->date}}" class="form-control search-bar w-auto" id="date-range" placeholder="Select Date Range">
                                <button type="submit" class="btn btn-primary text-white search-btn">Filter</button>
                               
                                  <a type="button" href="{{route('admin.clinic')}}" class="btn btn-warning text-white clear-btn">Clear</a>
                                
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
                                <th>Clinic Name</th>
                                <th>Email</th>
                                <th>Total Jobs</th>
                                <th>Created</th>
                                <th>Vrification Status</th>
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
                                    @if($row->logo)
                                    <img src="{{config('filepaths.clinic.public_url') . $row->logo}}" alt="" class="rounded-circle me-2" width="40">
                                    @endif
                                    <div>
                                      <h6>{{$row->name}}</h6>
                                      <p>{{$row->recruiter ? $row->recruiter->name : ''}}</p>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <h6>{{$row->email}}</h6>
                                </td>
                                <td><a href="{{route('admin.job',['id' => $row->id,'module' => 'clinic'])}}">{{$row->job_count}}</a></td>
                                <td>{{date('d F Y', strtotime($row->created_at))}}</td>
                                <td>
                                @if($row->verification)
                                <span class="badge bg-success text-white">Verified</span>
                                @else
                                <span class="badge bg-warning text-white">Pending</span>
                                @endif
                                </td>
                                <td>
                                @if($row->status)
                                <span class="badge bg-success text-white">Active</span>
                                @else
                                <span class="badge bg-warning text-white">Inactive</span>
                                @endif
                                </td>
                                <td>
                                  <div class="d-flex align-items-center">
                                 {{------ <a href="javascript:void(0);" class="btn btn-sm btn-primary text-white me-2 editRecruiter" 
                                      data-row="{{ json_encode($row) }}" title="Edit">
                                      <i class="mdi mdi-pencil menu-icon"></i>
                                  </a>------}}

                                      <a href="javascript:void(0);" class="btn btn-sm btn-warning text-white me-2 viewData" 
                                          data-row="{{ json_encode($row) }}" title="Edit">
                                          <i class="mdi mdi-eye menu-icon"></i>
                                      </a>

                                       
                                        @if($row->status)
                                        <a href="{{ route('clinic.status', ['id' => $row->id]) }}" class="btn btn-sm btn-success text-white me-2" id="active_link-{{$row->id}}" title="Activate">
                                            <i id="active_ids-{{$row->id}}" class="mdi mdi-toggle-switch menu-icon"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('clinic.status', ['id' => $row->id]) }}" class="btn btn-sm btn-secondary text-white me-2" id="deactive_link-{{$row->id}}"  title="Activate">
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
{{--------<div class="modal fade" id="editRecruiterModal" tabindex="-1" aria-labelledby="editRecruiterLabel" aria-hidden="true">
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
</div>------}}

<!-- Edit Recruiter Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="editRecruiterLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content cusomize-clinic-model">
      <div class="modal-header">
        <h5 class="modal-title" id="editRecruiterLabel">View Recruiter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="name" class="form-label">Clinic Name</label>
                <input type="text" class="form-control" id="clinic_name" disabled>
              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="name" class="form-label">Recruiter Name</label>
                <input type="text" class="form-control" id="owner_name" disabled>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" disabled>
              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Phone</label>
                <input type="email" class="form-control" id="phone" disabled>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Bio</label>
                <textarea id="bio" class="form-control" disabled></textarea>

              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Address</label>
                <textarea id="address" class="form-control" disabled></textarea>

              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12">
              <div class="mb-3">
                <label class="form-label">Documents</label>
                <div id="document_list"></div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label class="form-label">Verification Status</label>
                <div>
                  <span id="verification_status" class="badge"></span>
                </div>
              </div>
            </div>
            <div class="col-lg-6 col-md-6">
              <div class="mb-3">
                <label class="form-label">&nbsp;</label>
                <div>
                  <a href="#" id="verify_now_link" class="btn btn-sm btn-primary d-none">Verify Now</a>
                </div>
              </div>
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
   var module = '';
   var ActivateUrl = "{{ route('clinic.active') }}";
   var DeActivateUrl = "{{ route('clinic.de-active') }}";
   var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{asset('admin/assets/js/index.js')}}"></script>
<script>
  $(document).ready(function () {
    // Listen for file input change
    let baseUrl = "{{config('filepaths.clinic.public_url')}}";
    //view clinic
    $(".viewData").on("click", function () {
        let response = $(this).data("row");

        $("#clinic_name").val(response.name);
        $("#owner_name").val(response.recruiter.name);
        $("#email").val(response.email);
        $("#phone").val(response.phone);
        $("#bio").val(response.bio);
        $("#address").val(response.address);

        // Set verification status with badge color
        if (response.verification) {
            $("#verification_status").text("Verified").removeClass("bg-danger").addClass("bg-success");
            $("#verify_now_link").addClass("d-none"); // Hide the verify link
        } else {
            $("#verification_status").text("Pending").removeClass("bg-success").addClass("bg-danger");
            $("#verify_now_link").removeClass("d-none").attr("href","clinic/verify/" + response.id);
        }

        if (response.profile) {
            $("#recruiterProfileImage").attr("src", baseUrl + response.profile).show();
        } else {
            $("#recruiterProfileImage").hide();
        }

        // Display documents
        let documentList = $("#document_list");
        documentList.empty(); // Clear previous data

        if (response.document) {
            let documents = response.document.split(","); // Convert string to array

            documents.forEach(function (doc) {
                let docUrl = baseUrl + doc.trim(); // Adjust the path
                let fileExt = doc.split('.').pop().toLowerCase(); // Get file extension

                if (["jpg", "jpeg", "png", "gif"].includes(fileExt)) {
                    // Show image preview
                    documentList.append(`<a href="${docUrl}" target="_blank">
                        <img src="${docUrl}" alt="Document" class="img-thumbnail" width="100">
                    </a> `);
                } else {
                    // Show as a clickable link for PDFs, Word files, etc.
                    documentList.append(`<a href="${docUrl}" target="_blank" class="d-block">
                        📄 ${doc.trim()}
                    </a>`);
                }
            });
        } else {
            documentList.html('<p class="text-muted">No documents available.</p>');
        }

        $("#viewModal").modal("show");
    });
 
    // Submit Edit Form
    $("#editRecruiterForm").on("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{route('clinic.update')}}",  // Adjust your update route
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
