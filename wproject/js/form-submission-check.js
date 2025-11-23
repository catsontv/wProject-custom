/* Disable form submission unelss something has actually changed */
$('.general-form')
    .each(function(){
    $(this).data('serialized', $(this).serialize())
})
.on('change input click', function(){
    $(this)				
    .find('input:submit, button:submit')
    .attr('disabled', $(this).serialize() == $(this).data('serialized'));
})
$('.material-items .remove, .subtask-items .remove').click(function() {
    $('input:submit, button:submit').attr('disabled', false);
})
.find('input:submit, button:submit')
.attr('disabled', true);