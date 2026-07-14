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
                <h4 class="card-title card-title-dash">Settings</h4>

                <div class="table-responsive mt-3">
                  <table class="table select-table">
                    <thead>
                      <tr>
                        <th>S.No</th>
                        <th>Logo</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Facebook</th>
                        <th>Twitter</th>
                        <th>Instagram</th>
                        <th>LinkedIn</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($settings as $index => $row)
                      <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                          @if($row->logo)
                            <img src="{{ asset('uploads/settings/' . $row->logo) }}" alt="Logo" width="50">
                          @else
                            N/A
                          @endif
                        </td>
                        <td>{{ $row->email }}</td>
                        <td>{{ $row->phone }}</td>
                        <td>{{ $row->facebook ?? 'N/A' }}</td>
                        <td>{{ $row->twiter ?? 'N/A' }}</td>
                        <td>{{ $row->instagram ?? 'N/A' }}</td>
                        <td>{{ $row->linkdine ?? 'N/A' }}</td>
                       
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                  <div>Showing {{ $settings->firstItem() }} to {{ $settings->lastItem() }} of {{ $settings->total() }} entries</div>
                  <div>{{ $settings->links() }}</div>
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
