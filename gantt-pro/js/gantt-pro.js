var number_of_tasks = $('.gantt').length;
//console.log(number_of_tasks);

function isEmpty( el ){
    return !$.trim(el.html())
}
if (isEmpty($('.gantt-container .gantt'))) {
    //console.log('is empty');
    $('.update-gantt-pro-form').remove();
}