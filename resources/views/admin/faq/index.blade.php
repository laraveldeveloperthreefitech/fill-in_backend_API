@extends('admin.layout.app')
@section('content')

<div class="content-wrapper">
  <div class="row">
    <div class="col-sm-12">
      <div class="home-tab">
        <div class="tab-content tab-content-basic">
          <div class="tab-pane fade show active" id="overview">
            <div class="card card-rounded">
              <div class="card-body">
                <h4 class="card-title card-title-dash">FAQ List</h4>
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
                                  <a type="button" href="{{route('admin.faq.index',['id' => request()->id,'module' => request()->module])}}" class="btn btn-warning text-white clear-btn">Clear</a>
                                @else
                                  <a type="button" href="{{route('admin.faq.index')}}" class="btn btn-warning text-white clear-btn">Clear</a>
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
                            <div class="mb-1 text-end">
                  <a href="{{ route('admin.faq.create') }}" class="btn btn-primary text-white">+ Add FAQ</a>
                </div>
                        </div>

              

                <div class="table-responsive mt-3">
                  <table class="table select-table">
                    <thead>
                      <tr>
                      <th>
                        <input type="checkbox" id="check-all">
                      </th> 
                       <th>S.No</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($data as $index => $row)
                      <tr id="row-{{ $row->id }}">
                      <td><input type="checkbox" name="ids" class="child-checkbox" value="{{ $row->id }}"></td>
                      <td>{{ $data->firstItem() + $index }}</td>
                        <td>{{ $row->question }}</td>
                        <td>{{ $row->answer }}</td>
                        <td>{{ $row->role == 0 ? 'Recruiter' : 'Candidate' }}</td>
                        <td>
                          @if($row->status)
                            <span class="badge bg-success text-white">Active</span>
                          @else
                            <span class="badge bg-warning text-white">Inactive</span>
                          @endif
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            {{-- Edit --}}
                            <a href="{{ route('admin.faq.edit', $row->id) }}" class="btn btn-sm btn-primary text-white me-2" title="Edit">
                              <i class="mdi mdi-pencil menu-icon"></i>
                            </a>

                            
                            @if($row->status)
                                        <a href="{{ route('faq.status', ['id' => $row->id]) }}" class="btn btn-sm btn-success text-white me-2" id="active_link-{{$row->id}}" title="Activate">
                                            <i id="active_ids-{{$row->id}}" class="mdi mdi-toggle-switch menu-icon"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('faq.status', ['id' => $row->id]) }}" class="btn btn-sm btn-secondary text-white me-2" id="deactive_link-{{$row->id}}"  title="Activate">
                                            <i id="deactive_ids-{{$row->id}}" class="mdi mdi-toggle-switch-off menu-icon"></i>
                                        </a>
                                        @endif

                            {{-- Delete --}}
                            <a href="javascript:void(0);" 
                                      class="btn btn-sm btn-danger text-white me-2" 
                                      onclick="deleteConfirmation('{{ $row->id }}')"
                                      id="rocordDelete{{ $row->id }}"
                                      data-url="{{ route('admin.faq.delete', ['id' => $row->id]) }}">
                                  <i class="mdi mdi-delete menu-icon"></i>
                              </a>
                           {{-- --- " method="POST" onsubmit="return confirm('Are you sure?')" style="display:inline;">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-danger text-white" title="Delete">
                                <i class="mdi mdi-delete menu-icon"></i>
                              </button>
                            </form>--}}
                          </div>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                  <div>Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of {{ $data->total() }} entries</div>
                  <div>{{ $data->links() }}</div>
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
  <!-- SweetAlert Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Custom Config for Module -->
  <script>
      var module = 'FAQ';
      var ActivateUrl = "{{ route('faq.active') }}";
      var DeActivateUrl = "{{ route('faq.de-active') }}";
      var csrfToken = "{{ csrf_token() }}";
  </script>

  <!-- External JS File -->
  <script src="{{ asset('admin/assets/js/index.js') }}"></script>
@endpush  
