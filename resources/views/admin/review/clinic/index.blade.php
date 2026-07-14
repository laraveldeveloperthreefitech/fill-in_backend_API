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
                        <h4 class="card-title card-title-dash">Clinic Review List</h4>
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <!-- Left-aligned Form -->
                            <form action="{{ route('admin.clinicReview') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                <input type="text" name="search" value="{{request()->search}}" class="form-control search-bar w-auto" placeholder="Search...">
                                
                                {{------<input type="text" name="date" value="{{request()->date}}" class="form-control search-bar w-auto" id="date-range" placeholder="Select Date Range">-----}}
                                <button type="submit" class="btn btn-primary text-white search-btn">Filter</button>
                                <a type="button" href="{{route('admin.clinicReview')}}" class="btn btn-warning text-white clear-btn">Clear</a>
                            </form>

                            <!-- Right-aligned Buttons -->
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                            {{----------<button class="btn btn-outline-primary addSpecialization" >Add</button>
                                <button class="btn btn-success" id="activateAllSelected">Active</button>
                                <button class="btn btn-warning" id="inactivateAllSelected">Inactive</button>
                                <button class="btn btn-danger">Delete</button>
                                <button class="btn btn-info">Export</button>
                                <button class="btn btn-outline-primary">Import</button>----------}}
                            </div>
                        </div>



                        <div class="table-responsive mt-3">
                        <table class="table select-table">
                            <thead>
                              <tr>
                               
                                <th>S.No.</th>
                                <th>Candidate</th>
                                <th>Clinic</th>
                                <th>Image</th>
                                <th>Comment</th>
                                <th>Rate</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody id="indexData">
                              @foreach($data as $index =>$row)
                              <tr>
                                <td>{{$data->firstItem() + $index}}</td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <div>
                                      <h6>{{$row->candidate ? $row->candidate->name : ''}}</h6>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <div>
                                      <h6>{{$row->clinic ? $row->clinic->name : ''}}</h6>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <div>
                                        @if($row->image)
                                        <img src="{{config('filepaths.review.public_url') . $row->image}}" alt="">
                                        @endif
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <div>
                                    {{$row->comment}}
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($row->rate >= $i) 
                                            <span style="color:#F7941D;">
                                                <i class="menu-icon mdi mdi-star"></i> <!-- Filled Star -->
                                            </span>
                                        @else
                                            <span style="color:#F7941D;">
                                                <i class="menu-icon mdi mdi-star-outline"></i> <!-- Outline Star -->
                                            </span>
                                        @endif
                                    @endfor


                                    </div>
                                  </div>
                                </td>
                                
                                <td>
                                  <div class="d-flex align-items-center">
                                       <a href="javascript:void(0);" 
                                                class="btn btn-sm btn-danger text-white me-2" 
                                                onclick="deleteConfirmation('{{ $row->id }}')"
                                                id="rocordDelete{{ $row->id }}"
                                                data-url="{{ route('candidateReview.delete', ['id' => $row->id]) }}">
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

<!-- Edit specialization Modal -->
<div class="modal fade" id="specializationModal" tabindex="-1" aria-labelledby="specializationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content cusomize-specialization-model">
      <div class="modal-header">
        <h5 class="modal-title" id="specualizationLabel"> </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addSpecializationForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="id" id="specialization_id">
          
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
   var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{asset('admin/assets/js/index.js')}}"></script>
<script>
  $(document).ready(function () {
    // Listen for file input change
    let baseUrl = "{{config('filepaths.specialization.public_url')}}";
   
    $(".addSpecialization").on("click", function () {
        $('#addSpecializationForm')[0].reset();
        $("#specializationModal").modal("show");
        $('#specualizationLabel').html('Add Specialization');
    });

    // Open Edit Modal & Fetch Data
    $(".editSpecialization").on("click", function () {
        $('#addSpecializationForm')[0].reset();
        let response = $(this).data("row");
        $("#specialization_id").val(response.id);
        $("#name").val(response.name);
        $('#specualizationLabel').html('Edit Specialization');
        $("#specializationModal").modal("show");
       
    });

    // Submit Edit Form
    $("#addSpecializationForm").on("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "",  // Adjust your update route
            type: "POST",
            data: formData,
            processData: false,  // Prevent jQuery from processing the data
            contentType: false,  // Prevent jQuery from setting content-type
            success: function (response) {
               if(response.status){
                  successMsg("specialization Updated successfully!");
                  $("#specializationModal").modal("hide");
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
