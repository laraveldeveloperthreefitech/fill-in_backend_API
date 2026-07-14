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
                        <h4 class="card-title card-title-dash">Recruiter Roport List</h4>
                        
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <!-- Left-aligned Form -->
                            <form action="" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                <input type="text" name="search" value="{{request()->search}}" class="form-control search-bar w-auto" placeholder="Search...">
                                <!--<input type="text" name="date" value="{{request()->date}}" class="form-control search-bar w-auto" id="date-range" placeholder="Select Date Range">-->
                                <button type="submit" class="btn btn-primary text-white search-btn">Filter</button>
                                <a href="{{route('reportOnCandidate.index')}}" class="btn btn-warning text-white clear-btn">Clear</a>
                            </form>
                        </div>

                        <div class="table-responsive mt-3">
                          <table class="table select-table">
                            <thead>
                              <tr>
                                <th>S.No.</th>
                                <th>Candidate</th>
                                <th>Clinic</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Image</th>                             
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody id="indexData">
                              @foreach($reports as $index =>$row)
                              <tr id="row-{{$row->id}}">
                                <td>{{$reports->firstItem() + $index}}</td>
                                <td>{{$row->candidate ? $row->candidate->name : ''}}</td>
                                <td>{{$row->recruiter ? ($row->recruiter->clinic ? $row->recruiter->clinic->name : '') : ''}}</td>
                                <td>{{$row->title}}</td>
                                <td>{{$row->description}}</td>
                                <td>
                                    @if($row->image)
                                    <img src="{{config('filepaths.review.public_url') . $row->image}}" alt="" width="50">
                                    @endif
                                </td>
                                <td>
                                <a href="javascript:void(0);" 
                               class="btn btn-sm btn-danger text-white me-2" 
                               onclick="deleteConfirmation('{{ $row->id }}')"
                               id="rocordDelete{{ $row->id }}"
                               data-url="{{ route('ReportOnRecruiter.delete', ['id' => $row->id]) }}">
                              <i class="mdi mdi-delete menu-icon"></i>
                            </a>
                                </td>
                              </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>

                        <!-- Bootstrap Pagination -->
                        <div class="float-start mt-3">     
                           Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} entries
                        </div>
                        <div class="float-end mt-3">
                            {{ $reports->links() }}
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
   var module = 'ReportOnRecruiter';
   var ActivateUrl = "";
   var DeActivateUrl = "";
   var csrfToken = "{{ csrf_token() }}";
</script>
<script src="{{asset('admin/assets/js/index.js')}}"></script>

@endpush

