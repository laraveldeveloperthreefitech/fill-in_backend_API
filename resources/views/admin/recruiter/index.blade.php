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
                        <h4 class="card-title card-title-dash">Recuiter List</h4>
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <!-- Left-aligned Form -->
                            <form action="{{ route('admin.recuirter') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-0">

    <input type="text" 
           name="search" 
           value="{{ request()->search }}" 
           class="form-control search-bar w-auto" 
           placeholder="Search...">

    <select name="status" class="form-control search-bar w-auto">
        <option value="">Select Status</option>
        <option value="1" {{ request()->status === '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ request()->status === '0' ? 'selected' : '' }}>De-Active</option>
    </select>

    <input type="text" 
           name="date" 
           value="{{ request()->date }}" 
           class="form-control search-bar w-auto" 
           id="date-range" 
           placeholder="Select Date Range">

    <button type="submit" class="btn btn-primary text-white">Filter</button>

    <a href="{{ route('admin.recuirter') }}" class="btn btn-warning text-white">Clear</a>

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
                                <th>Primary Name</th>
                                <th>Practice Name</th>
                                <th>Email</th>
                                <th>Total Jobs</th>
                                <th>Verification</th>
                                <th>Created</th>
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
                                    @if($row->profile)
                                    <img src="{{config('filepaths.recruiter.public_url') . $row->profile}}" alt="" class="rounded-circle me-2" width="40">
                                    @endif
                                    <div>
                                      <h6>{{$row->name}}</h6>
                                    </div>
                                  </div>
                                </td>
                                
                                 <td>
                                  <h6>{{$row->clinic ? $row->clinic->name : ''}}</h6>
                                </td>
                                <td>
                                  <h6>{{$row->email}}</h6>
                                </td>
                                <td><a href="{{ $row->clinic && $row->clinic->job ? route('admin.job', ['id' => $row->clinic->id, 'module' => 'clinic']) : '#' }}">{{$row->clinic ? count($row->clinic->job) : 0}}</a></td>
                                <td>
                                @if($row->verified)
                                    <span class="badge bg-success text-white">
                                        Verified <i class="mdi mdi-check menu-icon"></i>
                                    </span>
                                @else
                                    <span class="badge bg-warning text-white">Pending..</span>
                                @endif

                                </td>
                                <td>{{date('d F Y', strtotime($row->created_at))}}</td>
                                <td>
                                @if($row->status)
                                <span class="badge bg-success text-white">Active</span>
                                @else
                                <span class="badge bg-warning text-white">Inactive</span>
                                @endif
                                </td>
                                <td>
                                  <div class="d-flex align-items-center">
                                    <a href="{{route('recruiter.view',$row->id)}}" class="btn btn-sm btn-warning text-white me-2" 
                                      title="Edit">
                                      <i class="mdi mdi-eye menu-icon"></i>
                                  </a>
                                  <a href="{{route('recruiter.edit',['id' =>$row->id])}}" class="btn btn-sm btn-primary text-white me-2 editRecruiter" 
                                      data-row="{{ json_encode($row) }}" title="Edit">
                                      <i class="mdi mdi-pencil menu-icon"></i>
                                  </a>

                                       
                                        @if($row->status)
                                        <a href="{{ route('recruiter.status', ['id' => $row->id]) }}" class="btn btn-sm btn-success text-white me-2" id="active_link-{{$row->id}}" title="Activate">
                                            <i id="active_ids-{{$row->id}}" class="mdi mdi-toggle-switch menu-icon"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('recruiter.status', ['id' => $row->id]) }}" class="btn btn-sm btn-secondary text-white me-2" id="deactive_link-{{$row->id}}"  title="Activate">
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
                                      {{ $data->withQueryString()->links() }}
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

<



@endsection
@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   var module = 'Recruiter';
   var ActivateUrl = "{{ route('recruiter.active') }}";
   var DeActivateUrl = "{{ route('recruiter.de-active') }}";
   var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{asset('admin/assets/js/index.js')}}"></script>

@endpush
