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
                        <h4 class="card-title card-title-dash">Profession List</h4>
                        
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
                                <a type="button" href="{{route('admin.profession')}}" class="btn btn-warning text-white clear-btn">Clear</a>
                            </form>

                            <!-- Right-aligned Buttons -->
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <button class="btn btn-outline-primary addprofession" >Add</button>
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
                                <th>Icon</th>
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
                                  @if($row->logo)
                                  <img src="{{config('filepaths.profession.public_url') .$row->logo}}" alt="">
                                  @else
                                    No image
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
                                  <a href="javascript:void(0);" class="btn btn-sm btn-primary text-white me-2 editprofession" 
                                      data-row="{{ json_encode($row) }}" title="Edit">
                                      <i class="mdi mdi-pencil menu-icon"></i>
                                  </a>

                                       
                                        @if($row->status)
                                        <a href="{{ route('profession.status', ['id' => $row->id]) }}" class="btn btn-sm btn-success text-white me-2" id="active_link-{{$row->id}}" title="Activate">
                                            <i id="active_ids-{{$row->id}}" class="mdi mdi-toggle-switch menu-icon"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('profession.status', ['id' => $row->id]) }}" class="btn btn-sm btn-secondary text-white me-2" id="deactive_link-{{$row->id}}"  title="Activate">
                                            <i id="deactive_ids-{{$row->id}}" class="mdi mdi-toggle-switch-off menu-icon"></i>
                                        </a>
                                        @endif
                                       <a href="javascript:void(0);" 
                                                class="btn btn-sm btn-danger text-white me-2" 
                                                onclick="deleteConfirmation('{{ $row->id }}')"
                                                id="rocordDelete{{ $row->id }}"
                                                data-url="{{ route('profession.delete', ['id' => $row->id]) }}">
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

<!-- Edit profession Modal -->
<div class="modal fade" id="professionModal" tabindex="-1" aria-labelledby="professionLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content cusomize-specialization-model">
      <div class="modal-header">
        <h5 class="modal-title" id="specualizationLabel"> </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addprofessionForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="id" id="profession_id">
          
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name">
          </div>

           <div class="mb-3">
            <label for="name" class="form-label">logo</label>
            <input type="file" accept="image/*" class="form-control" id="image" name="logo">
          </div>
          <div class="mb-3">
            <img id="logoPreview" src="" alt="Logo Preview" style="max-width: 150px; display: none; border: 1px solid #ddd; padding: 5px;">
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
   var module = 'profession';
   var ActivateUrl = "{{ route('profession.active') }}";
   var DeActivateUrl = "{{ route('profession.de-active') }}";
   var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{asset('admin/assets/js/index.js')}}"></script>
<script>
  $(document).ready(function () {
    // Listen for file input change
    let baseUrl = "{{config('filepaths.profession.public_url')}}";
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
   
    $(".addprofession").on("click", function () {
        $('#addprofessionForm')[0].reset();
        $("#professionModal").modal("show");
          $('#logoPreview').hide().attr('src', '');  
        $('#specualizationLabel').html('Add Profession');
    });

    // Open Edit Modal & Fetch Data
    $(".editprofession").on("click", function () {
        $('#addprofessionForm')[0].reset();
        let response = $(this).data("row");
        $("#profession_id").val(response.id);
        $("#name").val(response.name);
        // Assuming 'response.department_id' contains the ID you want to set
        $('#department').val(response.department_id).trigger('change');
        if (response.logo) {
            var image = baseUrl + response.logo
            $("#logoPreview").attr("src", image).show();
        }
        $('#specualizationLabel').html('Edit Profession');
        $("#professionModal").modal("show");
       
    });
    $("#image").on("change", function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#logoPreview").attr("src", e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });


    // Submit Edit Form
    $("#addprofessionForm").on("submit", function (e) {
        e.preventDefault();

        // Assuming you have a form with an input type="file" and other fields
      let formData = new FormData();
      formData.append('name', $('#name').val()); // other fields
      formData.append('id', $('#profession_id').val());
      // Get image file from input
      let image = $('#image')[0].files[0]; // <input type="file" id="image">
      if (image) {
          formData.append('image', image);
      }

      $.ajax({
          url: "{{ route('profession.update') }}",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          success: function (response) {
              if (response.status) {
                  successMsg("Profession updated successfully!");
                  $("#professionModal").modal("hide");
                  window.location.reload();
              } else {
                  errorMsg("Something went wrong!");
              }
          },
          error: function (xhr, status, error) {
              var response = xhr.responseJSON;
              if (response && response.errors) {
                  $.each(response.errors, function (field, errors) {
                      errorMsg(errors[0]);
                  });
              } else {
                  errorMsg("Something went wrong!");
              }
          }
      });

    });

});

</script>

@endpush
