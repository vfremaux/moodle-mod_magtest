/**
 *
 */
// jshint undef:false, unused:false
/* globals $ */

$(document).ready(function() {

    // Confirm delete action.
    $('.questioncommands #delete').click(function(event){
        event.preventDefault();
        var result = confirm("Are you sure you want to delete this question?");
        if (result) {
            window.location = $(this).attr('href');
        }
    });

    // Confirm delete action.
    $('.categorycommands #delete').click(function(event) {

        event.preventDefault();
        var result = confirm("Are you sure you want to delete this category?");
        if (result) {
            window.location = $(this).attr('href');
        }
    });
});