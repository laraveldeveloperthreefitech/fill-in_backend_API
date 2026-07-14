@extends('admin.layout.app')
@section('content')

          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="home-tab">
                  <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    
                  </div>
                  <div class="tab-content tab-content-basic">
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="statistics-details d-flex align-items-center justify-content-between">
                          @foreach($card as $title => $count)
                              <div class="{{ in_array($title, ['Verified Recruiter', 'Total Jobs', 'Active Jobs']) ? 'd-none d-md-block' : '' }}">
                                  <p class="statistics-title">{{ $title }}</p>
                                  <h3 class="rate-percentage">{{ $count }}</h3>
                              </div>
                          @endforeach
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-lg-12 d-flex flex-column">
                          <div class="row flex-grow">
                            <div class="col-12 grid-margin stretch-card">
                              <div class="card card-rounded">
                                <div class="card-body">
                                  <div class="d-sm-flex justify-content-between align-items-start">
                                    <div>
                                    <h4 class="card-title card-title-dash text-primary fw-bold">
                                        ? Top Responded Jobs
                                    </h4>
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        ? Last 30 Days: {{ count($data) }} Jobs
                                    </span>
                                    </div>
                                   
                                  </div>
                                  <div class="table-responsive  mt-1">
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
                                        </tr>
                                      </thead>
                                      <tbody id="indexData">
                                        @foreach($data as $index =>$row)
                                        <tr>
                                          <td><input type="checkbox" name="ids" class="child-checkbox" value="{{ $row->id }}"></td>
                                          <td>{{$index + 1}}</td>
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
                                            <h6>{{$row->department ? $row->department->name : ''}}</h6>
                                          </td>
                                          <td>{{$row->expire_date ? date('d F Y', strtotime($row->expire_date)) : ''}}</td>
                                          <td>{{$row->candidates_count}}</td>
                                         
                                        @endforeach
                                      </tbody>
                                    </table>
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
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
        

        @endsection