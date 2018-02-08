// Code goes here

$(document).ready(function(){
  var table = $('#logs').DataTable();
  console.log("exportation");
  
  $('#btn-export').on('click', function(){
      $('#logs').append(table.$('tr').clone()).table2excel({
          exclude: ".excludeThisClass",
          name: "Worksheet Name",
          filename: "SomeFile" //do not include extension
      });
  });      
})
