function disableUI() {
    $('.left, .middle, .right, header').addClass('disable_ui').addClass('blur');
}
function enableUI() {
    $('.left, .middle, .right, header').removeClass('disable_ui').removeClass('blur');
}

jQuery(document).ready(function($) {

	/* Show spinner when submitting any form */
	$('form').on('submit', function(e) {
		$('#status-update').hide();
		$('.working').addClass('rotate');
	});

	var response;

	/* Start User Account */ 
	$('#account-form').on('submit', function(e) {
		e.preventDefault();
		var formData = false;
		if (window.FormData) {
			if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
				var $inputs = $('input[type="file"]:not([disabled])', $(this));
				$inputs.each(function(_, input) {
					if (input.files.length > 0) return $(input).prop('disabled', true);
				});
			}
			formData = new FormData($(this)[0]);
			if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
				$inputs.prop('disabled', false);
			}
			formData.append('nonce', inputs.nonce);
			formData.append('action', 'update_user_details');
		} else {
			formData = $(this).serialize();
			formData += '&nonce=' + inputs.nonce;
			formData += '&action=update_user_details';
		}
		disableUI();
		$.ajax({
			url: inputs.ajaxurl,
			cache: false,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				//console.log(response);
				ResponseSuccess(response);
				$('.working').removeClass('rotate');
				enableUI();
			}
		});

		return false;

	});
	/* End User Account */ 


    /* Transfer Projects */ 
	$('#projects-transfer-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();

		$.post( inputs.ajaxurl, {
			action : 'transfer_projects',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		return false;

	});
	/* End Transfer Projects */ 

    /* Switch Theme */ 
	$('#switch-theme-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'change_theme',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            $('#switch-theme-form').addClass('switch-theme-form-facade');
            enableUI();
		});		

		return false;
	});
	/* End Switch Theme */ 

    /* Manage files */ 
	$('#manage-files').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'manage_files',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});		

		return false;
	});
	/* End Switch Theme */ 

	/* Start New Project */ 
	$('#new-project-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'add_new_project',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End New Project */ 

	/* Start Extend Project */ 
	$('#extend-project-deadline').on('submit', function(e) {
		e.preventDefault();
        disableUI();

		$.post( inputs.ajaxurl, {
			action : 'extend_project_deadline',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End Extend Project */ 

    /* Start Archive Project */ 
	$('#archive-project').on('submit', function(e) {
		e.preventDefault();
        disableUI();

		$.post( inputs.ajaxurl, {
			action : 'archive_project',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End Archive Project */ 

    /* Start Delete Completed Tasks in Project */ 
	$('#delete-completed-project-tasks').on('submit', function(e) {
		e.preventDefault();
        disableUI();

		$.post( inputs.ajaxurl, {
			action : 'delete_completed_project_tasks',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End Delete Completed Tasks in Project */ 

    /* Start Update Gantt Pro */ 
	$('#update-gantt-pro-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'update_gantt_pro',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End Update Gantt Pro */ 


	/* Start Edit Project */ 
	$('#edit-project-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();

		$.post( inputs.ajaxurl, {
			action : 'edit_project',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End Edit Project */ 


	/* Start Delete Project */ 
	$('#delete-project-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'delete_project',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End Delete Project */ 

	/* Start Delete Task */ 
	$('#delete-task-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'delete_task',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End Delete Task */ 


	/* Add New Task */ 
	$('#new-task-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
		
		var ajaxurl 	= $('#ajax_url').val();
		var form_data 	= $('#new-task-form')[0];
		var Form		= new FormData(form_data);
		var file_upload	= $('#task_files').files;
			
		Form.append('file', file_upload);
		Form.append('action', 'add_new_task');
		
		$.ajax({
			cache: false,
			url: ajaxurl,
			type: 'post',
			data:  Form,
			processData: false,
			contentType: false,
			
			success: function(response) {
				ResponseSuccess(response);
				$('.working').removeClass('rotate');
                enableUI();
			}
		});
		
		return false;

	});
	/* End New Task */  

	
	/* Edit Task */ 
	$('#edit-task-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		var ajaxurl 	= $('#ajax_url').val();
		var form_data 	= $('#edit-task-form')[0];
		var Form		= new FormData(form_data);
		var file_upload	= $('#task_files').files;
			
		Form.append('file', file_upload);
		Form.append('action', 'edit_task');
		
		$.ajax({
			cache: false,
			url: ajaxurl,
			type: 'post',
			data:  Form,
			processData: false,
			contentType: false,
			
			success: function(response) {
				ResponseSuccess(response);
				$('.working').removeClass('rotate');
                enableUI();
			}
		});	
        
		return false;

	});
	/* End Edit Task */ 

	/* Delete File */ 
	$('#delete-file').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'delete_file',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});		

		return false;
	});
	/* End Delete File */ 

    /* Delete Comment */ 
	$('#comment-threads-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'delete_comment',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});		

		return false;
	});
	/* End Delete Comment */ 

    /* Start Comment Status */ 
	$('#comment-status-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'comment_status',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		return false;

	});
	/* End Comment Status */ 

    /* Start Delete Time */ 
	$('#edit-time-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'delete_time',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});

		//console.log(response.status);

		return false;

	});
	/* End Delete Time */ 


	/* Start change task status (from the single task page) */ 
	$('#change-task-status').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'change_task_status',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});		

		return false;
	});
	/* End change task status */ 

	/* Arrange the Kanban */ 
	$('#arrange-kanban').on('submit', function(e) {
		e.preventDefault();
        disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'kanban_arranged',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});		

		return false;
	});
	/* End Arrange the Kanban */ 

	/* Update Subtask List */ 
	$('#update-subtask-list').on('submit', function(e) {
		e.preventDefault();
        //disableUI();
   
		$.post( inputs.ajaxurl, {
			action : 'update_subtask_list',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            //enableUI();
		});	

		return false;
	});
	/* End Update Subtask List */ 

	/* Start Update Task Status */ 
	$('.update-task-status-form').on('submit', function(e) {
		e.preventDefault();
        //disableUI();
	
		$.post( inputs.ajaxurl, {
			action : 'update_task_status',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            //enableUI();
		});
		return false;

	});
	/* End Update Task Status */ 

	/* Start Claim Task */ 
	$('.claim-task-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();

		$.post( inputs.ajaxurl, {
			action : 'claim_task',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});
		return false;

	});
	/* End Claim Task */ 

	/* Start Request Task Takeover */ 
	$('#request-task-takeover-form').on('submit', function(e) {
		e.preventDefault();
        disableUI();

		$.post( inputs.ajaxurl, {
			action : 'request_task_takeover',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});
		return false;

	});
	/* End Request Task Takeover */ 


	/* Start Mark Message As Read */ 
	$('.mark-message-read').on('submit', function(e) {
		e.preventDefault();
        //disableUI();
	
		$.post( inputs.ajaxurl, {
			action : 'mark_message_read',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            //disableUI();
		});
		return false;

	});
	/* End Mark Message As Read */ 

	/* Start Task Takeover Choice */ 
	$('#transfer-task-ownership').on('submit', function(e) {
		e.preventDefault();
        disableUI();
	
		$.post( inputs.ajaxurl, {
			action : 'transfer_task_ownership',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            enableUI();
		});
		return false;

	});
	/* End Task Takeover Choice */ 
	

	/* Start Timer */ 
	$('#timer-start').on('submit', function(e) {
		e.preventDefault();

		$.post( inputs.ajaxurl, {
			action : 'start_timer',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            $('.task-in-progress .timer').addClass('spin');
		});
		
		//console.log($('#task_project').val());		

		return false;
	});
	/* Start Timer */ 

	
	/* Stop Timer */ 
	$('#timer-stop').on('submit', function(e) {
		e.preventDefault();

		$.post( inputs.ajaxurl, {
			action : 'stop_timer',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            $('.task-in-progress .timer').removeClass('spin');
		});
		
		//console.log($('#task_project').val());		

		return false;
	});
	/* Stop Timer */ 	

    /* Start Missed Time */ 
	$('#add-missed-time').on('submit', function(e) {
		e.preventDefault();

		$.post( inputs.ajaxurl, {
			action : 'add_missed_time',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
		});
		
		//console.log($('#task_project').val());		

		return false;
	});
	/* End Missed Time */ 

    /* Start Favs */ 
	$('#task-follow, #follows-form').on('submit', function(e) {
		e.preventDefault();
        //disableUI();

		$.post( inputs.ajaxurl, {
			action : 'task_follow_status',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			//console.log(response);
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
            //enableUI();
		});
		
		//console.log($('#follow_status').val());		

		return false;
	});

	/* Onboarding */ 
	$('#onboarding').on('submit', function(e) {
		e.preventDefault();

		$.post( inputs.ajaxurl, {
			action : 'complete_onboarding',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
		});	
		return false;
	});
	/* Onboarding */ 	

	/* Mark all messages as read */ 
	$('#read-all-messages-form').on('submit', function(e) {
		e.preventDefault();

		$.post( inputs.ajaxurl, {
			action : 'read_all_messages',
			nonce : inputs.nonce,
			post : $(this).serialize()
		},
		function(response) {
			ResponseSuccess(response);
			$('.working').removeClass('rotate');
		});	
		return false;
	});
	/* Onboarding */ 	



	/*** Responses ********************************/ 
	function ResponseSuccess(data) {

		response = JSON.parse(data);

		$('#status-update').removeClass();
		$('#status-update').addClass('status');
		$('#status-update').css('display', 'flex');

		if (response.status === 'success') {
			
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');

		/* 
			Task created.
			Some sneaky stuff going on here, using the 'task created' as the response.status 
			so that those words can also be used in the returned notification. 
			Example: Fortify the cube task created.
			This comes at the sacrifice of using the response to increment the task count
			in the projects nav, but it's a much better use.
		*/
		} else if (response.status === 'task created') {
				
			$('#status-update').text(response.message + ' ' + response.status);
			$('#status-update').addClass('success');

			/* Disable the submit button */
			$('.new-task-form .submit button').attr('disabled','disabled');

			/* Clear some fields field */
            $("#new-task-form")[0].reset();
			// $('.new-task-form input[name="task_name"]').val('');
			// $('.new-task-form select[name="task_project"]').val('');
			// $('.new-task-form textarea[name="task_description"]').val('');
			// $('.new-task-form input[name="task_start_date"]').val('');
			// $('.new-task-form input[name="task_end_date"]').val('');
			// $('.new-task-form input[name="task_job_number"]').val('');
			// $('.new-task-form input[name="task_pc_complete"]').val('');
			// $('.new-task-form input[name="task_priority"]').prop('checked', false);
			// $('.new-task-form select[name="task_status"]').val('not-started');
			// $('.new-task-form input[name="task_milestone"]').prop('checked', false);
			// $('.new-task-form input[name="task_private"]').prop('checked', false);
			// $('.new-task-form select[name="task_relation"]').val('');
			// $('.new-task-form select[name="task_related"]').val('');
			// $('.new-task-form textarea[name="task_explanation"]').val('');
			// $('.new-task-form #task_files').val('');

            $('.new-task-form select[name="task_related"]').removeAttr('required');
			$('.new-task-form .subtask-items li').remove();
			$('.new-task-form label').removeClass('selected');

			/* Enable the submit button when the task field is in focus */
			$('.new-task-form input').focus(function() {
				$('.new-task-form .submit button').removeAttr('disabled');
			});

			/* Insert sidebar menu item to edit the task that was just created */
			$('.right ul').prepend('<li><a href="'+response.data+'"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square feather-icon" color="#ff9800"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg><span class="spawn">'+response.message+'</span></a></li>');

			/* Add one to the current task count in the correct project side nav */
			// var taskCount = $('#project-'+response.data+' span i').text();
			// var newTaskCount = +taskCount + +1;
			// $('#project-'+response.data+' span i').text(newTaskCount);

		/* Switch theme */
        } else if (response.status === 'success-theme-switched') {
		$('#status-update').text(response.message);
		$('#status-update').addClass('success');
		
        /* File Management */
        } else if (response.status === 'success-files-deleted') {
            $('.files').remove();
            $('.tab-nav .files').remove();
            $('#manage-files .delete-all-files, #manage-files .download-all-files').remove();
            $('.tab-nav .task-specs').addClass('active');
            $('.tab-content .task-specs').addClass('active');
            $('.tabby .tab-content').css('display', 'block');
            $('#status-update').text(response.message);

        /*TODO: Do something when downloading all files */
        } else if (response.status === 'success-files-downloading') {
            $('#status-update').text(response.message);

		/*TODO: Task Exists */
		} else if (response.status === 'task-exists') {
		//$('#status-update').text(response.message + ' ' + response.data);
		$('#status-update').text(response.message);
		$('#status-update').addClass('error');
		
		/* Task edited */
		} else if (response.status === 'success-edit-task') {
			
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
			$('.right li').fadeIn();

            /* Replace the file input (to avoid multiple task edits including the files each time) */
            var replacement_file_input = $('<input type="file" name="task_files[]" multiple="multiple" class="file-input" />');
            $('.edit-task-form .file-input').replaceWith(replacement_file_input);

			/* Replace the H1 text with the updated task name */
			$('.middle h1').text(response.data);

		/* Task status updated (from the task solo page) */
		} else if (response.status === 'success-change-task-status') {
					
			$('#status-update').text(response.message);
			// $('.side-form-box .radio').removeClass('selected');
			// $('.side-form-box .radio:nth-child(3)').addClass('selected');
			// $('.side-form-box .radio:nth-child(3) input').prop('checked', true);
			$('#status-update').addClass('success');
            $('.single-task-status').fadeOut();

        /* Transfer projects success */
		} else if (response.status === 'success-projects-transferred') {
					
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');		
            $('.transfer-projects').fadeOut();

        /* Transfer projects failed */
        } else if (response.status === 'failed-projects-transferred') {
					
			$('#status-update').text(response.message);
			$('#status-update').addClass('error');			

		/* Extend project */
		} else if (response.status === 'success-project-extended') {
					
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
			$('.side-notice').fadeOut();
			$('.project-details .due-date').removeClass('overdue');	
			$('.project-details .due-date span').html('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar feather-icon" color="#ff9800"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>'+response.message);			

		/* Task claimed (from project page) */
		} else if (response.status === 'success-claim-task') {
					
			$('#status-update').text('Task claimed: '+response.data);
			$('#status-update').addClass('success');

		/* When subtasks are all complete */
		} else if (response.status === 'success-task-status-complete-time-enabled-wip') {
					
			$('#timer-stop').hide();
			$('#timer-start').show();
			$('#timer-start .timer span').removeClass('running');
			$('.message-count').css('display', 'none');
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
            
            
            enableUI();
			timer.pause();

         } else if (response.status === 'success-task-status-complete') {
					
            $('.message-count').css('display', 'none');
            $('#status-update').text(response.message);
            $('#status-update').addClass('success');
            enableUI();

		/* Task status deleted (from the single task page) */
		} else if (response.status === 'success-deleted-task') {
						
			$('.timer-ui, .task-in-progress, .right ul, .right .owner, .all-comments, #respond').remove();
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
	
        
		} else if (response.status === 'success-update-subtask-list') {

			$('#status-update').text(response.message);
			$('#status-update').addClass('success');

		/* Task status updated */
		} else if (response.status === 'success-update-task-status') {
					
			$('#status-update').text(response.message);
			filter_counts();
			$('#status-update').addClass('success');
            
        /* Fav added */
        } else if (response.status === 'success-added-fav') {
                            
            $('#status-update').text(response.message);
            $('#status-update').addClass('success');

        /* Missed time added */
        } else if (response.status === 'success-missed-time-added') {
                            
            $('#status-update').text(response.message);
            $('#status-update').addClass('success');
            window.location.href="?tab=time";

        /* Comment deleted */
        } else if (response.status === 'success-comment-deleted') {
                            
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
			$('#comment-'+response.data).fadeOut(500);
        
		/* Comment delete failed */
		} else if (response.status === 'success-comment-delete-failed') {
										
			$('#status-update').text(response.message);
			$('#status-update').addClass('error');

        /* Comment status */
		} else if (response.status === 'success-comments-status-updated') {
										
			window.location.href = window.location.pathname;

        /* Fav removed */
        } else if (response.status === 'success-removed-fav') {
                            
            $('#status-update').text(response.message);
            $('#status-update').addClass('success');

        /* Gantt Pro updated */
        } else if (response.status === 'success-gantt-pro-updated') {
                            
			$('.gantt-pro-mask').removeClass('show');
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');

			/* 
				This is required!
				For some reason, the gantt pro hidden fields won't get updated values
				when dragging the same gantt bar consecutively.
				To 'fix' this, we clear specific hidden input values after we gave gotten them.
			*/
			$('.update-gantt-pro-form #project_id, .update-gantt-pro-form #project_start_date, .update-gantt-pro-form #project_end_date, .update-gantt-pro-form #project_pc_complete, .update-gantt-pro-form #project_name, .update-gantt-pro-form #gantt_mode, .update-gantt-pro-form #task_id, .update-gantt-pro-form #task_start_date, .update-gantt-pro-form #task_end_date, .update-gantt-pro-form #task_pc_complete, .update-gantt-pro-form #task_name, .update-gantt-pro-form #gantt_mode').val('');

		/* Project deleted */
		} else if (response.status === 'success-project-deleted') {

			/* Fadeout these elements */
			$('.main-progress, .status-box, .gantt, .update-gantt-pro-form, .toggle-gantt-fs, .toggle-gantt-visibility, .tabby, .tips, .right, .projects-list .current, .middle h1, .project-description, .update-gantt-pro-form, .project-finished').fadeOut();
					
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');

		/* Task deleted */
		} else if (response.status === 'success-task-deleted') {
					
			$('.edit-task-form, .middle h1, .message-count.clock, .right ul').fadeOut();
			$('#status-update').text(response.message);
			filter_counts();
			$('#status-update').addClass('success');

		/* File deleted */
		} else if (response.status === 'success-file-deleted') {
							
			$('#file-'+response.data).fadeOut(200);
			var files_count = $('.tab-nav .files span').text();

			var new_files_count = +files_count + -1;
			$('.tab-nav .files span').text(new_files_count);
			
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
			
			
			//console.log(new_files_count);


		/* Project created */
		} else if (response.status === 'success-project') {
			/* Add one to the current project count in the left pane. */
			var projectCount = $('.project-count').attr('data');
			var newProjectCount = +projectCount + +1;
			$('.project-count').text(newProjectCount);
			$('.main-nav ul').prepend(response.data); // See $project_nav in functions-ajax.php


			/* Clear some fields field */
			$('.new-project-form input[name="project_name"]').val('');
			$('.new-project-form input[name="description"]').val('');
			$('.new-project-form textarea[name="project_full_description"]').val('');
			$('.new-project-form select[name="project_status"]').val('');
			$('.new-project-form input[name="project_start_date"]').val('');
			$('.new-project-form input[name="project_end_date"]').val('');
			$('.new-project-form input[name="project_time_allocated"]').val('');
			$('.new-project-form input[name="project_job_number"]').val('');
			$('.new-project-form select[name="project_manager"]').val('');
			$('.new-project-form .material-items li').remove();
			$('.new-project-form select[name="task_group"]').val('');
			$('.new-project-form select[name="task-ownership"]').val('');
			$('.new-project-form .materials-tally').text('');

			$('#status-update').text(response.message);
			$('#status-update').addClass('success');

		/* Project edited */
		} else if (response.status === 'success-edit-project') {
			/* Replace the H1 text with the updated project name */
			//$('.middle h1').text(response.data);

			var messageCount = $('.notify .message-count').text();
			var newMessageCount = +messageCount + +1;
			$('.notify .message-count').text(newMessageCount);

			$('#status-update').text(response.message);
			$('#status-update').addClass('success');	

        /* Project archived */
		} else if (response.status === 'success-archive-project') {
			
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');	
            $('.project-page .middle, .right').css('filter', 'grayscale(100%)');
            $('.project-page .middle, .right').css('pointer-events', 'none');

            setTimeout(function() { 
                $('.project-page .project-finished').fadeOut();
            }, 300);

            setTimeout(function() { 
                $('.project-page .main-progress').fadeOut();
            }, 600);

            setTimeout(function() { 
                $('.project-page .status-box').fadeOut();
            }, 900);

            setTimeout(function() { 
                $('.project-page .tab-nav').fadeOut();
            }, 1200);

            setTimeout(function() { 
                $('.project-page .update-task-status-form').fadeOut();
            }, 1500);

            setTimeout(function() { 
                $('.project-page .right').fadeOut();
            }, 1800);

        /* Delete completed project tasks */
        } else if (response.status === 'success-deleted-completed-project-tasks') {
                    
            $('#status-update').text(response.message);
            $('#status-update').addClass('success');	

            setTimeout(function() { 
                $('.tab-content li.complete').fadeOut();
            }, 300);

		/* Task Ownership Requested */
		} else if (response.status === 'success-request-task-ownership') {

			$('#status-update').text(response.message);
			$('#status-update').addClass('success');	
			$('.right .notice form button').attr('disabled', 'disabled').css('opacity', '.3');

		/* Task Ownership Decision Made */
		} else if (response.status === 'task-takeover-decision') {

			$('#status-update').text(response.message);
			$('#status-update').addClass('success');	

			$('.middle .waiting-notification').removeClass('waiting').addClass('decided').fadeOut(1000);
			location.reload();

		/* Mark Message Read */
		} else if (response.status === 'success-message-read') {

			$('.message-'+response.data).closest('div').fadeOut(100);
			var message_count = $('.notify .message-count').text();

			/* Prevent count from going below 1 */
			if(message_count < 1) {
				message_count = 1;
                $('header .icons b').addClass('fade');
                $('header .icons .message-count').remove();
                $('.notifications').remove();
			} else {
				message_count = message_count;
			}
			
			setTimeout(function() { 
				$('.notify .message-count').text(message_count - 1);
			}, 250);

			$('#status-update').text(response.message);
			$('#status-update').addClass('success');


		//$('.middle .waiting-notification').removeClass('waiting').addClass('decided').fadeOut(1000);

		/* Arranged Kanban */
		} else if (response.status === 'success-kanban-arranged') {

			$('#status-update').text(response.message);
			$('#status-update').addClass('success');	

		/* Start Timer */
		} else if (response.status === 'success-timer-started') {

            $('.side-form.change-task-status').addClass('disabled');
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');	
			$('.timer span').addClass('running');
			$('.timer-start').hide();
			$('.timer-stop').show();
			$(this).attr('disabled');
			var message = $('.task-in-progress').attr('data');
			$('.work-in-progress div').text(message);
			$('.task-in-progress .message-count').addClass('clock').show();
            $('.waiting-notification .approve').css('pointer-events', 'none').css('opacity', '.3');

            /* Switch focus to timer tab */
            $('.tab-nav li').removeClass('active');
            $('.tab-nav .task-time').addClass('active');
            $('.tab-content').removeClass('active');
            $('.tab-content-task-time').addClass('active');
            $('.task-time').removeClass('fade');

			$('.side-form-box label input').prop('checked', false);
			$('.side-form-box label input[value="in-progress"]').prop('checked', true);
            $('.side-form-box label').removeClass('selected');
            $('.side-form-box label input[value="in-progress"]').closest('label').addClass('selected');
            
			/* Update the task status when starting timer */
			setTimeout(function() { 
				$('#change-task-status').submit();
			}, 500);

            /* Insert row */
            var the_time = $('.task-time em').text();
            var time_user_name = $('.current-user-data').data('name');
            var time_avatar = $('.current-user-data').data('avatar');
            var time_date = $('.current-user-data').data('date');
            // $('<li class="inserted"><span><img src="'+time_avatar+'" class="avatar" />'+time_user_name+'</span><span class="time">'+the_time+'</span><span>'+time_date+'</span><span class="delete-time"></span></li>').insertAfter('.time-header');

		/* Stop Timer */
		} else if (response.status === 'success-timer-stopped') {

            timer.pause();
            $('.side-form.change-task-status').removeClass('disabled');
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
			$('.task-in-progress .message-count, .work-in-progress').hide();
			$('.timer span').removeClass('running');
			// $('.timer-start').show();
			// $('.timer-stop').hide();
			$('.work-in-progress div').text('');
			
            //location.reload();
            window.location.href = window.location.pathname+"?"+'tab=time';

			/* Update the task status when stopping timer */
			// setTimeout(function() { 
			// 	$('#change-task-status').submit();
			// 	$('.side-form-box input[value="incomplete"]').prop('checked', true);
			// }, 2500);
				
		/* Time deleted */
		} else if (response.status === 'success-time-deleted') {       
            
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
			$('.edit-time-form li .delete-time').removeClass('deleting-in-progress');
			
            /*
                Note: I have commented out $('.the-time').text(result) on single-task.php
                and am reloading the page after time is deleted instead.

                This is because of some inconsistent behavior I've seen on one clients website.
                So instead of trying to update the total time (in real-time) after each time entry is deleted,
                just reload the page instead after each time entry is deleted.
            */
            window.location.href = window.location.pathname+"?"+'tab=time';

		/* Time edited */
		} else if (response.status === 'success-time-edited') {       
					
			$('#status-update').text(response.message);
			$('#status-update').addClass('success');
			//location.reload(); //TODO: Don't reload the page if it's possible to just update the time in the table footer.
            window.location.href = window.location.pathname+"?"+'tab=time';

        /* Time edit failed */
		} else if (response.status === 'success-time-edit-failed') {       
					
			$('#status-update').text(response.message);
			$('#status-update').addClass('alert');
			//location.reload(); //TODO: Don't reload the page if it's possible to just update the time in the table footer.
            //window.location.href = window.location.pathname+"?"+'tab=time';

		/* Failed Timer */
		} else if (response.status === 'failed-timer-started') {

			$('#status-update').text(response.message);
			$('#status-update').addClass('error');	
			$('.timer span').removeClass('running');
			timer.pause();
			$('.timer-ui .task-time em').text('00:00:00');

        /* Followed task */
		} else if (response.status === 'success-followed-task') {

            $('.follow-task-'+response.data).closest('div').fadeOut(100);
			var message_count = $('.my-favs .message-count.follows').text();

			/* Prevent count from going below 1 */
			if(message_count < 1) {
				message_count = 1;
                $('header .icons .my-favs b').addClass('fade');
                $('header .icons .my-favs .message-count.follows').remove();
                $('.my-follows').remove();
			} else {
				message_count = message_count;
			}
			
			setTimeout(function() { 
				$('.my-favs .message-count.follows').text(parseFloat(message_count) + parseFloat(1));
			}, 250);

			$('#status-update').text(response.message);
            $('#status-update').addClass('success');	
        
        /* Unfollowed task */
        } else if (response.status === 'success-unfollowed-task') {

            $('.follow-task-'+response.data).closest('div').fadeOut(100);
			var message_count = $('.my-favs .message-count.follows').text();

			/* Prevent count from going below 1 */
			if(message_count < 1) {
				message_count = 1;
                $('header .icons .my-favs b').addClass('fade');
                $('header .icons .my-favs .message-count.follows').remove();
                $('.my-follows').remove();
			} else {
				message_count = message_count;
			}
			
			setTimeout(function() { 
				$('.my-favs .message-count.follows').text(parseFloat(message_count) - parseFloat(1));
			}, 250);

			$('#status-update').text(response.message);
            $('#status-update').addClass('success');	

		/* Complete Onboarding */
		} else if (response.status === 'complete-onboarding') {

			$('#status-update').text(response.message);
			location.reload();

		/* Error */
		} else if (response.status === 'error') {

			$('#status-update').text(response.message);
			$('#status-update').addClass('error');

		/* Message read */
		} else if (response.status === 'success-messages-read') {       
				
			$('.message-count').text('0');
			$('.notifications.dropdown').fadeOut();
			$('.notifications.dropdown div').fadeOut();
			$('.notifications.dropdown h3 em').fadeOut();

			setTimeout(function() { 
				$('.notifications.dropdown').removeAttr('style');
			}, 500);

			$('#status-update').text(response.message);
			$('#status-update').addClass('success');

		
		} else {

			$('#status-update').text(response.message);
			$('#status-update').addClass('alert');
		}

		/* Response message fadeout timer is in functions.php (search for #status-update) */

	} 

});