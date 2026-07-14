<!-- partial:partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item {{ Route::currentRouteName() == 'admin.home' ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('admin.home') }}">
        <i class="mdi mdi-grid-large menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <li class="nav-item {{ Route::currentRouteName() == 'admin.recuirter' ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('admin.recuirter') }}">
        <i class="mdi mdi-briefcase menu-icon"></i>
        <span class="menu-title">Recruiter</span>
      </a>
    </li>

    <li class="nav-item {{ Route::currentRouteName() == 'admin.candidate' ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('admin.candidate') }}">
        <i class="mdi mdi-account menu-icon"></i>
        <span class="menu-title">Candidate</span>
      </a>
    </li>

    {{-- 
    <li class="nav-item {{ Route::currentRouteName() == 'admin.clinic' ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('admin.clinic') }}">
        <i class="mdi mdi-medical-bag menu-icon"></i>
        <span class="menu-title">Clinic</span>
      </a>
    </li> 
    --}}

    <li class="nav-item {{ Route::currentRouteName() == 'admin.job' ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('admin.job') }}">
        <i class="mdi mdi-clipboard-text menu-icon"></i>
        <span class="menu-title">Jobs</span>
      </a>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.candidateReview') || request()->routeIs('admin.clinicReview') ? 'active' : '' }}">
      <a class="nav-link" data-bs-toggle="collapse" href="#ratings-menu"
         aria-expanded="{{ request()->routeIs('admin.candidateReview') || request()->routeIs('admin.clinicReview') ? 'true' : 'false' }}"
         aria-controls="ratings-menu">
        <i class="menu-icon mdi mdi-star"></i>
        <span class="menu-title">Ratings</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse {{ request()->routeIs('admin.candidateReview') || request()->routeIs('admin.clinicReview') ? 'show' : '' }}" id="ratings-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.candidateReview') ? 'active' : '' }}" href="{{ route('admin.candidateReview') }}">Candidate Ratings</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.clinicReview') ? 'active' : '' }}" href="{{ route('admin.clinicReview') }}">Clinic Ratings</a>
          </li>
        </ul>
      </div>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.reportOnJob.index') || request()->routeIs('reportOnCandidate.index') || request()->routeIs('ReportOnRecruiter.index') ? 'active' : '' }}">
      <a class="nav-link" data-bs-toggle="collapse" href="#report-menu"
         aria-expanded="{{ request()->routeIs('admin.reportOnJob.index') || request()->routeIs('reportOnCandidate.index') || request()->routeIs('ReportOnRecruiter.index')?  'true' : 'false' }}"
         aria-controls="report-menu">
        <i class="mdi mdi-clock-time-four menu-icon"></i>
        <span class="menu-title">Reports List</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse {{ request()->routeIs('admin.reportOnJob.index') || request()->routeIs('reportOnCandidate.index') || request()->routeIs('ReportOnRecruiter.index') ? 'show' : '' }}" id="report-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.reportOnJob.index') ? 'active' : '' }}" href="{{ route('admin.reportOnJob.index') }}">Report on Jobs</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reportOnCandidate.index') ? 'active' : '' }}" href="{{ route('reportOnCandidate.index') }}">Report on Candidate</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('ReportOnRecruiter.index') ? 'active' : '' }}" href="{{ route('ReportOnRecruiter.index') }}">Report on Recruiter</a>
          </li>
        </ul>
      </div>
    </li>

    <li class="nav-item {{ Route::currentRouteName() == 'admin.language' ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('admin.language') }}">
        <i class="mdi mdi-translate menu-icon"></i>
        <span class="menu-title">Language</span>
      </a>
    </li>

    <li class="nav-item {{ request()->routeIs(['admin.vaccination', 'admin.qualification', 'admin.location', 'admin.software', 'admin.department', 'admin.profession', 'admin.employment', 'admin.document']) ? 'active' : '' }}">
      <a class="nav-link" data-bs-toggle="collapse" href="#catalogue-menu"
         aria-expanded="{{ request()->routeIs(['admin.vaccination', 'admin.qualification', 'admin.location', 'admin.software', 'admin.department', 'admin.profession', 'admin.employment', 'admin.document']) ? 'true' : 'false' }}"
         aria-controls="catalogue-menu">
        <i class="menu-icon mdi mdi-star"></i>
        <span class="menu-title">Catalogue</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse {{ request()->routeIs(['admin.vaccination', 'admin.qualification', 'admin.location', 'admin.software', 'admin.department', 'admin.profession', 'admin.employment', 'admin.document']) ? 'show' : '' }}" id="catalogue-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item no-bg {{ Route::currentRouteName() == 'admin.vaccination' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.vaccination') }}">
              <i class="mdi mdi-needle menu-icon"></i> <span class="menu-title">Vaccination</span>
            </a>
          </li>
          <li class="nav-item no-bg {{ Route::currentRouteName() == 'admin.qualification' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.qualification') }}">
              <i class="mdi mdi-certificate menu-icon"></i> <span class="menu-title">Qualification</span>
            </a>
          </li>
          <li class="nav-item no-bg {{ Route::currentRouteName() == 'admin.location' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.location') }}">
              <i class="mdi mdi-map-marker menu-icon"></i> <span class="menu-title">Location Range</span>
            </a>
          </li>
          <li class="nav-item no-bg {{ Route::currentRouteName() == 'admin.software' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.software') }}">
              <i class="mdi mdi-application menu-icon"></i> <span class="menu-title">Softwares</span>
            </a>
          </li>
          <li class="nav-item no-bg {{ Route::currentRouteName() == 'admin.department' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.department') }}">
              <i class="mdi mdi-stethoscope menu-icon"></i> <span class="menu-title">Practice Role</span>
            </a>
          </li>
          <li class="nav-item no-bg {{ Route::currentRouteName() == 'admin.profession' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.profession') }}">
              <i class="mdi mdi-stethoscope menu-icon"></i> <span class="menu-title">Profession</span>
            </a>
          </li>
          {{-- 
          <li class="nav-item no-bg {{ Route::currentRouteName() == 'admin.employment' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.employment') }}">
              <i class="mdi mdi-clock-time-four menu-icon"></i> <span class="menu-title">Employment Type</span>
            </a>
          </li>
          <li class="nav-item no-bg {{ Route::currentRouteName() == 'admin.document' ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.document') }}">
              <i class="mdi mdi-file-document menu-icon"></i> <span class="menu-title">Required Document</span>
            </a>
          </li>
          --}}
        </ul>
      </div>
    </li>

    <li class="nav-item {{ request()->routeIs('clinic-support.index') || request()->routeIs('candidate-support.index') ? 'active' : '' }}">
      <a class="nav-link" data-bs-toggle="collapse" href="#support-menu"
         aria-expanded="{{ request()->routeIs('clinic-support.index') || request()->routeIs('candidate-support.index') ? 'true' : 'false' }}"
         aria-controls="support-menu">
        <i class="mdi mdi-clock-time-four menu-icon"></i>
        <span class="menu-title">Support</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse {{ request()->routeIs('clinic-support.index') || request()->routeIs('candidate-support.index') ? 'show' : '' }}" id="support-menu">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('clinic-support.index') ? 'active' : '' }}" href="{{ route('clinic-support.index') }}">Clinic Support</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('candidate-support.index') ? 'active' : '' }}" href="{{ route('candidate-support.index') }}">Candidate Support</a>
          </li>
        </ul>
      </div>
    </li>

    <li class="nav-item {{ Route::currentRouteName() == 'admin.faq.index' ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('admin.faq.index') }}">
        <i class="mdi mdi-clipboard-text menu-icon"></i>
        <span class="menu-title">FAQ</span>
      </a>
    </li>

    <li class="nav-item {{ Route::currentRouteName() == 'admin.setting.form' ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('admin.setting.form') }}">
        <i class="mdi mdi-clipboard-text menu-icon"></i>
        <span class="menu-title">Setting</span>
      </a>
    </li>
  </ul>
</nav>
