$(document).ready(function(){

    //confirm delete action 
    $('.questioncommands #delete').click(function(event){
       
       event.preventDefault(); 
       var result = confirm("Are you sure you want to delete this question?"); 
       if(result)
       {
           window.location = $(this).attr('href');
       }
        
    });
    
        //confirm delete action 
    $('.categorycommands #delete').click(function(event){
       
       event.preventDefault(); 
       var result = confirm("Are you sure you want to delete this category?"); 
       if(result)
       {
           window.location = $(this).attr('href');
       }
        
    });
    
    
});