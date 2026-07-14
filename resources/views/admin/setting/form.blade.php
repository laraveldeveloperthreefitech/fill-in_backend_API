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
                <h4 class="card-title card-title-dash">Add Settings</h4>

                <form action="{{ route('admin.setting.store') }}" method="POST" enctype="multipart/form-data">
                  @csrf

                  <div class="mb-3">
                    <label for="about_us" class="form-label">About Us</label>
                    <textarea class="form-control summernote" name="about_us" id="about_us">
                    {{ old('about_us', $setting->about_us ?? '') }}</textarea>
                  </div>

                  <div class="mb-3">
                    <label for="privacy_policy" class="form-label">Privacy Policy</label>
                    <textarea name="privacy_policy" class="form-control summernote" rows="3" required>{{ old('privacy_policy', $setting->privacy_policy ?? '') }}</textarea>
                  </div>

                  <div class="mb-3">
                    <label for="terms_conditions" class="form-label">Terms & Conditions</label>
                    <textarea class="form-control summernote" name="terms_conditions" id="terms_conditions">
                    {{ old('terms_conditions', $setting->terms_conditions ?? '') }}</textarea>
                  </div>

                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $setting->email ?? '') }}" required>
                  </div>

                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $setting->phone ?? '') }}" required>
                  </div>

                  <div class="mb-3">
                    <label for="facebook" class="form-label">Facebook</label>
                    <input type="text" name="facebook" class="form-control" value="{{ old('facebook', $setting->facebook ?? '') }}">
                  </div>

                  <div class="mb-3">
                    <label for="twitter" class="form-label">Twitter</label>
                    <input type="text" name="twitter" class="form-control" value="{{ old('twitter', $setting->twitter ?? '') }}">
                  </div>

                  <div class="mb-3">
                    <label for="instagram" class="form-label">Instagram</label>
                    <input type="text" name="twitter" class="form-control" value="{{ old('twitter', $setting->twitter ?? '') }}">
                    </div>

                  <div class="mb-3">
                    <label for="linkedin" class="form-label">LinkedIn</label>
                    <input type="text" name="linkedin" class="form-control" value="{{ old('linkedin', $setting->linkedin ?? '') }}">
                    </div>

                  <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control">
                    @if(!empty($setting->logo))
                      <img src="{{ asset($setting->logo) }}" alt="Logo" width="80" class="mt-2">
                    @endif
                  </div>

                  <button type="submit" class="btn btn-primary">Submit</button>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
<!-- Summernote CSS -->
@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
@endpush

<!-- Summernote JS and jQuery -->
 @push('script')
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
  <script>
    $(document).ready(function() {
      $('.summernote').summernote({
        height: 200
      });
    });
  </script>
@endpush