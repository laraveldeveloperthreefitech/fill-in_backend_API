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
                        <h4 class="card-title card-title-dash">Candidate Review List</h4>
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <!-- Left-aligned Form -->
                            <form action="" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                <input type="text" name="search" value="{{request()->search}}" class="form-control search-bar w-auto" placeholder="Search...">
                                {{------<select name="status" class="form-control search-bar w-auto" id="">
                                  <option value="">Select Status</option>
                                  <option value="1" {{request()->status == 1 ? 'selected' : ''}}>Active</option>
                                  <option value="0" {{isset(request()->status) && request()->status == 0 ? 'selected' : ''}}>De-Active</option>
                                </select>
                               <input type="text" name="date" value="{{request()->date}}" class="form-control search-bar w-auto" id="date-range" placeholder="Select Date Range">-----}}
                                <button type="submit" class="btn btn-primary text-white search-btn">Filter</button>
                                <a type="button" href="{{route('admin.candidateReview')}}" class="btn btn-warning text-white clear-btn">Clear</a>
                            </form>

                            <!-- Right-aligned Buttons -->
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                            {{---------- <button class="btn btn-outline-primary addSpecialization" >Add</button>
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
                                <th>Clinic Name</th>
                                <th>Candidate</th>
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
                                      <h6>{{$row->clinic ? $row->clinic->name : ''}}</h6>
                                    </div>
                                  </div>
                                </td>
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


@endsection
@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   var module = 'Specialization';
   var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{asset('admin/assets/js/index.js')}}"></script>


@endpush
