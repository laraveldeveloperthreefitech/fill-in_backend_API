<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{RecruiterController,AuthController,CandidateController,ClinicController,JobController,SpecializationController,EmploymentTypeController,
    ReviewController,DocumentController,ClinicSupportController,CandidateSupportController,SettingController,FaqController,ReportOnCandidateController,Condidate_ReportController
    ,DepartmentController,SoftwareController,LanguageController,QualificationController,WorkLocationRangeController,VaccineController};




Route::get('/', function () {
    	
        return redirect('admin/login');
    });
Route::get('/admin', function () {
        return redirect('admin/login');
    });

Route::get('admin/login', [AuthController::class, 'login'])->name('login');
Route::post('store-timezone', [AuthController::class, 'updateTimezone']);
Route::post('login', [AuthController::class, 'auth'])->name('admin.auth');
Route::group(['prefix' => '/admin', 'middleware' => ['auth','admintimzone']], function () {

    // Route::get('/notifications/fetch', function () {
    //     return response()->json(Auth::user()->unreadNotifications);
    // });

    Route::get('/notifications/fetch', function () {
        $notifications = auth()->user()->notifications;
        return response()->json($notifications);
    });

	;
    

    Route::get('/notifications/mark-as-read/{id}', function ($id) {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['status' => 'read']);
    });
    

    Route::get('/home', [AuthController::class, 'home'])->name('admin.home');
    Route::get('/profile', [AuthController::class, 'profile'])->name('admin.profile');
    Route::post('/profile-update', [AuthController::class, 'update'])->name('admin.profile.update');
    Route::put('/change-password', [AuthController::class, 'changePassword'])->name('admin.profile.password');
    Route::get('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // Route::controller(RecruiterController::class)->group(function () {
    //     Route::get('recruiter','index')->name('admin.recuirter');
    //     Route::get('recruiter-status','changeStatus')->name('recruiter.status');
    //     Route::get('/admin/recruiters/edit','edit')->name('recruiter.edit');
    //     Route::post('recruiter-active','activeAll')->name('recruiter.active');
    //     Route::post('recruiter-de-active','deActiveAll')->name('recruiter.de-active');
    //     Route::post('recruiter-update','update')->name('recruiter.update');
    //     Route::get('view-recruiter/{id}','viewRecruiter')->name('recruiter.view');
    // });
    
     Route::prefix('admin')->controller(RecruiterController::class)->group(function () {
    Route::get('recruiter','index')->name('admin.recuirter');
    Route::get('recruiter-status','changeStatus')->name('recruiter.status');
    Route::get('recruiters/edit','edit')->name('recruiter.edit');
    Route::post('recruiter-active','activeAll')->name('recruiter.active');
    Route::post('recruiter-de-active','deActiveAll')->name('recruiter.de-active');
    Route::post('recruiter-update','update')->name('recruiter.update');
    Route::get('view-recruiter/{id}','viewRecruiter')->name('recruiter.view');
});
    
    Route::controller(CandidateController::class)->group(function () {
        Route::get('candidates','index')->name('admin.candidate');
        Route::get('candidate-status','changeStatus')->name('candidate.status');
        Route::post('candidate-active','activeAll')->name('candidate.active');
        Route::post('candidate-de-active','deActiveAll')->name('candidate.de-active');
        Route::get('candidate/edit','edit')->name('candidate.edit');
        Route::get('candidate/view','view')->name('candidate.view');
        Route::post('candidate-update','update')->name('candidate.update');
    });

    Route::controller(ClinicController::class)->group(function () {
        Route::get('clinics','index')->name('admin.clinic');
        Route::get('clinic-status','changeStatus')->name('clinic.status');
        Route::post('clinic-active','activeAll')->name('clinic.active');
        Route::post('clinic-de-active','deActiveAll')->name('clinic.de-active');
        Route::post('clinic-update','update')->name('clinic.update');
        Route::get('clinic/verify/{id}','verify')->name('clinic.verify');
    });

    Route::controller(JobController::class)->group(function () {
        Route::get('job','index')->name('admin.job');
        Route::get('job-status','changeStatus')->name('job.status');
        Route::post('job-active','activeAll')->name('job.active');
        Route::post('job-de-active','deActiveAll')->name('job.de-active');
        Route::post('job-update','update')->name('job.update');
        Route::get('view-job-details/{id}','viewJob')->name('view-job');
    });

    Route::controller(SpecializationController::class)->group(function () {
        Route::get('profession','index')->name('admin.profession');
        Route::get('profession-status','changeStatus')->name('profession.status');
        Route::post('profession-active','activeAll')->name('profession.active');
        Route::post('profession-de-active','deActiveAll')->name('profession.de-active');
        Route::post('profession-update','updateOrAdd')->name('profession.update');
        Route::get('profession-delete','delete')->name('profession.delete');
    });

    Route::controller(DepartmentController::class)->group(function () {
        Route::get('department','index')->name('admin.department');
        Route::get('department-status','changeStatus')->name('department.status');
        Route::post('department-active','activeAll')->name('department.active');
        Route::post('department-de-active','deActiveAll')->name('department.de-active');
        Route::post('department-update','updateOrAdd')->name('department.update');
        Route::get('department-delete','delete')->name('department.delete');
    });

    Route::controller(EmploymentTypeController::class)->group(function () {
        Route::get('employment','index')->name('admin.employment');
        Route::get('employment-status','changeStatus')->name('employment.status');
        Route::post('employment-active','activeAll')->name('employment.active');
        Route::post('employment-de-active','deActiveAll')->name('employment.de-active');
        Route::post('employment-update','updateOrAdd')->name('employment.update');
        Route::get('employment-delete','delete')->name('employment.delete');
    });

    Route::controller(SoftwareController::class)->group(function () {
        Route::get('software','index')->name('admin.software');
        Route::get('software-status','changeStatus')->name('software.status');
        Route::post('software-active','activeAll')->name('software.active');
        Route::post('software-de-active','deActiveAll')->name('software.de-active');
        Route::post('software-update','updateOrAdd')->name('software.update');
        Route::get('software-delete','delete')->name('software.delete');
    });

    Route::controller(LanguageController::class)->group(function () {
        Route::get('language','index')->name('admin.language');
        Route::get('language-status','changeStatus')->name('language.status');
        Route::post('language-active','activeAll')->name('language.active');
        Route::post('language-de-active','deActiveAll')->name('language.de-active');
        Route::post('language-update','updateOrAdd')->name('language.update');
        Route::get('language-delete','delete')->name('language.delete');
    });

    Route::controller(QualificationController::class)->group(function () {
        Route::get('qualification','index')->name('admin.qualification');
        Route::get('qualification-status','changeStatus')->name('qualification.status');
        Route::post('qualification-active','activeAll')->name('qualification.active');
        Route::post('qualification-de-active','deActiveAll')->name('qualification.de-active');
        Route::post('qualification-update','updateOrAdd')->name('qualification.update');
        Route::get('qualification-delete','delete')->name('qualification.delete');
    });

    Route::controller(WorkLocationRangeController::class)->group(function () {
        Route::get('location','index')->name('admin.location');
        Route::get('location-status','changeStatus')->name('location.status');
        Route::post('location-active','activeAll')->name('location.active');
        Route::post('location-de-active','deActiveAll')->name('location.de-active');
        Route::post('location-update','updateOrAdd')->name('location.update');
        Route::get('location-delete','delete')->name('location.delete');
    });
    
    Route::controller(VaccineController::class)->group(function () {
        Route::get('vaccination','index')->name('admin.vaccination');
        Route::get('vaccination-status','changeStatus')->name('vaccination.status');
        Route::post('vaccination-active','activeAll')->name('vaccination.active');
        Route::post('vaccination-de-active','deActiveAll')->name('vaccination.de-active');
        Route::post('vaccination-update','updateOrAdd')->name('vaccination.update');
        Route::get('vaccination-delete','delete')->name('vaccination.delete');
    });

    Route::controller(DocumentController::class)->group(function () {
        Route::get('document','index')->name('admin.document');
        Route::get('document-status','changeStatus')->name('document.status');
        Route::post('document-active','activeAll')->name('document.active');
        Route::post('document-de-active','deActiveAll')->name('document.de-active');
        Route::post('document-update','updateOrAdd')->name('document.update');
        Route::get('document-delete','delete')->name('document.delete');
    });

    Route::controller(ReviewController::class)->group(function () {
        Route::get('candidate-review','candidate')->name('admin.candidateReview');
        Route::get('candidate-review-delete','candidatedelete')->name('candidateReview.delete');
        Route::get('clinic-review','clinic')->name('admin.clinicReview');
        Route::get('clinic-review-delete','clinicdelete')->name('clinicReview.delete');
    }); 

    Route::controller(ReportOnCandidateController::class)->group(function () {
        Route::get('report-on-candidates', 'index')->name('reportOnCandidate.index');
        Route::get('can-rep-delete/{id}','destroy')->name('candRep.delete'); 
        Route::get('job-report-list', 'jobReportIndex')->name('admin.reportOnJob.index');
        Route::get('job-report-delete/{id}','jobReportDelete')->name('jobRep.delete'); 
        Route::get('report-on-recruiter','ReportOnRecruiter')->name('ReportOnRecruiter.index');  
        Route::get('recruiter-report-delete/{id}','deleteReportRecruiter')->name('ReportOnRecruiter.delete');   
    });

    Route::controller(FaqController::class)->group(function () {
        Route::get('faq', 'index')->name('admin.faq.index');
        Route::get('faq/create', 'create')->name('admin.faq.create'); 
        Route::get('faq/delete', 'destroy')->name('admin.faq.delete');
        Route::get('faq/edit/{id}', 'edit')->name('admin.faq.edit');
        Route::get('faq-status','changeStatus')->name('faq.status');
        Route::post('faq-active','activeAll')->name('faq.active');
        Route::post('faq-de-active','deActiveAll')->name('faq.de-active');
        Route::match(['post', 'put'], 'faq/save/{id?}', 'storeOrUpdate')->name('admin.faq.save');

    });


    Route::controller(CandidateSupportController::class)->group(function () {
       
        Route::get('/candidate-support', 'index')->name('candidate-support.index');
        Route::post('/candidate-support/respond','respond')->name('candidate-support.respond');
    });

    Route::controller(ClinicSupportController::class)->group(function () {
        Route::get('/clinic-support', 'index')->name('clinic-support.index');
        Route::post('/clinic-support/respond', 'respond')->name('clinic-support.respond'); 
    });

    Route::controller(SettingController::class)->group(function () {
        Route::get('settings', 'index')->name('setting.index');
        Route::get('admin/settings','form')->name('admin.setting.form');
        Route::post('admin/settings', 'store')->name('admin.setting.store');

    });
});