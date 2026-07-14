
    $(document).ready(function(){
        
        $('#activateAllSelected').click(function(e) {
            e.preventDefault();
            var all_ids = [];
            $('input:checkbox[name=ids]:checked').each(function() {
                all_ids.push($(this).val());
            });

            // Show confirmation dialog
            Swal.fire({
                title: `Activate Selected ${module}?`,
                text: `Are you sure you want to activate these ${module}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Activate',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    // User confirmed, proceed with AJAX request
                    $.ajax({
                        url: ActivateUrl,
                        type: "POST",
                        data: {
                            ids: all_ids,
                            _token: csrfToken
                        },
                        success: function(response) {
                            // Update UI if activation is successful
                            if(response.status){
                                $.each(all_ids, function(key, val) {
                                    $('#deactive_ids-' + val)
                                        .removeClass('mdi-toggle-switch-off').addClass(
                                            'mdi-toggle-switch');
                                    $('#deactive_link-' + val)
                                        .removeClass('btn-secondary').addClass(
                                            'btn-success');
                                });
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: `${module} have been activated.`,
                                }).then(function() {
                                    location.reload();
                                });
                                $('input:checkbox').prop('checked', false);
                                
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: `First choose the ${module}?.`,
                                });
                            }
                            
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: `First choose the ${module}?.`,
                            });
                        }
                    });
                }
            });
        });

        $('#inactivateAllSelected').click(function(e) {
            e.preventDefault();
            var all_ids = [];
            $('input:checkbox[name=ids]:checked').each(function() {
                all_ids.push($(this).val());
            });

            // Show confirmation dialog
            Swal.fire({
                title: `Deactivate Selected ${module}?`,
                text: `Are you sure you want to deactivate these ${module}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Deactivate',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    // User confirmed, proceed with AJAX request
                    $.ajax({
                        url: DeActivateUrl,
                        type: "POST",
                        data: {
                            ids: all_ids,
                            _token: csrfToken
                        },
                        success: function(response) {
                            // Update UI if deactivation is successful
                            if(response.status){
                                $.each(all_ids, function(key, val) {
                                    $('#active_ids-' + val)
                                        .removeClass('mdi-toggle-switch').addClass(
                                            'mdi-toggle-switch-off');
                                    $('#active_link-' + val)
                                        .removeClass('btn-success').addClass(
                                            'btn-secondary');
                                });
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: `${module} have been activated.`,
                                }).then(function() {
                                    location.reload();
                                });
                                $('input:checkbox').prop('checked', false);
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: `First choose the ${module}?.`,
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: `First choose the ${module}?.`,
                            });
                        }
                    });
                }
            });
        });
        
    });

    function deleteConfirmation(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: `Once deleted, you will not be able to recover this ${module} !`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            dangerMode: true,
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with the delete operation
                var url = $("#rocordDelete" + id).data('url');

                $.ajax({
                    url: url,
                    type: "GET",
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong!',
                        });
                    }
                });
            }
        });
    }