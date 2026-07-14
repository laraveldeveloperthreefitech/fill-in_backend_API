@extends('admin.layout.app')
@section('content')

<div class="content-wrapper">
  <div class="row">
    <div class="col-sm-12">
      <div class="card card-rounded">
        <div class="card-body">
          <h4 class="card-title">
            {{ isset($faq) ? 'Edit FAQ' : 'Add FAQ' }}
          </h4>

          {{-- Success message --}}
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <form action="{{ isset($faq) ? route('admin.faq.save', $faq->id) : route('admin.faq.save') }}" method="POST">
            @csrf
            @if(isset($faq))
              @method('PUT') {{-- Laravel will understand it's an update --}}
            @endif

            <div class="mb-3">
              <label>Question</label>
              <input type="text" name="question" class="form-control"
                     value="{{ old('question', isset($faq) ? $faq->question : '') }}" required>
            </div>

            <div class="mb-3">
              <label>Answer</label>
              <textarea name="answer" class="form-control" rows="4" required>{{ old('answer', isset($faq) ? $faq->answer : '') }}</textarea>
            </div>

            <div class="mb-3">
              <label>Status</label>
              <select name="status" class="form-control" required>
                <option value="1" {{ isset($faq) && $faq->status == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ isset($faq) && $faq->status == 0 ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            <div class="mb-3">
              <label>Role</label>
              <select name="role" class="form-control" required>
                <option value="0" {{ isset($faq) && $faq->role == 0 ? 'selected' : '' }}>Recruiter</option>
                <option value="1" {{ isset($faq) && $faq->role == 1 ? 'selected' : '' }}>Candidate</option>
              </select>
            </div>

            <div class="mb-3 text-end">
              <button type="submit" class="btn btn-success">
                {{ isset($faq) ? 'Update FAQ' : 'Add FAQ' }}
              </button>
              <a href="{{ route('admin.faq.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

@endsection
