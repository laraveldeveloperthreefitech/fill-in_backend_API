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
                        <h4 class="card-title card-title-dash">qualification List</h4>
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <!-- Left-aligned Form -->
                            <form action="" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                <input type="text" name="search" value="{{request()->search}}" class="form-control search-bar w-auto" placeholder="Search...">
                                <select name="status" class="form-control search-bar w-auto" id="">
                                  <option value="">Select Status</option>
                                  <option value="1" {{request()->status == 1 ? 'selected' : ''}}>Active</option>
                                  <option value="0" {{isset(request()->status) && request()->status == 0 ? 'selected' : ''}}>De-Active</option>
                                </select>
                                {{------<input type="text" name="date" value="{{request()->date}}" class="form-control search-bar w-auto" id="date-range" placeholder="Select Date Range">-----}}
                                <button type="submit" class="btn btn-primary text-white search-btn">Filter</button>
                                <a type="button" href="{{route('admin.qualification')}}" class="btn btn-warning text-white clear-btn">Clear</a>
                            </form>

                            <!-- Right-aligned Buttons -->
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <button class="btn btn-outline-primary addqualification" >Add</button>
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
                                <th>Name</th>
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
                                      <h6>{{$row->name}}</h6>
                                    </div>
                                  </div>
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
                                  <a href="javascript:void(0);" class="btn btn-sm btn-primary me-2 editqualification" 
                                      data-row="{{ json_encode($row) }}" title="Edit">
                                      <i class="mdi mdi-pencil menu-icon"></i>
                                  </a>

                                       
                                        @if($row->status)
                                        <a href="{{ route('qualification.status', ['id' => $row->id]) }}" class="btn btn-sm btn-success text-white me-2" id="active_link-{{$row->id}}" title="Activate">
                                            <i id="active_ids-{{$row->id}}" class="mdi mdi-toggle-switch menu-icon"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('qualification.status', ['id' => $row->id]) }}" class="btn btn-sm btn-secondary text-white me-2" id="deactive_link-{{$row->id}}"  title="Activate">
                                            <i id="deactive_ids-{{$row->id}}" class="mdi mdi-toggle-switch-off menu-icon"></i>
                                        </a>
                                        @endif
                                       <a href="javascript:void(0);" 
                                                class="btn btn-sm btn-danger text-white me-2" 
                                                onclick="deleteConfirmation('{{ $row->id }}')"
                                                id="rocordDelete{{ $row->id }}"
                                                data-url="{{ route('qualification.delete', ['id' => $row->id]) }}">
                                            <i class="mdi mdi-delete menu-icon"></i>
                                        </a>
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

<!-- Edit qualification Modal -->
<div class="modal fade" id="qualificationModal" tabindex="-1" aria-labelledby="qualificationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content cusomize-employment-model">
      <div class="modal-header">
        <h5 class="modal-title" id="specualizationLabel"> </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addqualificationForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="id" id="qualification_id">
          
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name">
          </div>
          
         
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   var module = 'qualification Type';
   var ActivateUrl = "{{ route('qualification.active') }}";
   var DeActivateUrl = "{{ route('qualification.de-active') }}";
   var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{asset('admin/assets/js/index.js')}}"></script>
<script>
  $(document).ready(function () {
    // Listen for file input change
    let baseUrl = "{{config('filepaths.qualification.public_url')}}";
   
    $(".addqualification").on("click", function () {
        $('#addqualificationForm')[0].reset();
        $("#qualificationModal").modal("show");
        $('#specualizationLabel').html('Add qualification Type');
    });

    // Open Edit Modal & Fetch Data
    $(".editqualification").on("click", function () {
        $('#addqualificationForm')[0].reset();
        let response = $(this).data("row");
        $("#qualification_id").val(response.id);
        $("#name").val(response.name);
        $('#specualizationLabel').html('Edit qualification Type');
        $("#qualificationModal").modal("show");
        
    });

    // Submit Edit Form
    $("#addqualificationForm").on("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{route('qualification.update')}}",  // Adjust your update route
            type: "POST",
            data: formData,
            processData: false,  // Prevent jQuery from processing the data
            contentType: false,  // Prevent jQuery from setting content-type
            success: function (response) {
               if(response.status){
                  successMsg("qualification Updated successfully!");
                  $("#qualificationModal").modal("hide");
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
