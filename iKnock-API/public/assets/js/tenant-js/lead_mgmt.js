$(document).ready(function(){

    $('.add-link').click(function(){
        var url = $(this).data('href')
        window.location.href = url;
    })

    $("#e2").daterangepicker({
        datepickerOptions : {
            numberOfMonths : 2
        }
    });

    
    $("#actionDateInput").daterangepicker({
         presetRanges: [{
             text: 'Today',
             dateStart: function() { return moment() },
             dateEnd: function() { return moment() }
         }, {
             text: 'Tomorrow',
             dateStart: function() { return moment().add('days', 1) },
             dateEnd: function() { return moment().add('days', 1) }
         }, {
             text: 'Next 7 Days',
             dateStart: function() { return moment() },
             dateEnd: function() { return moment().add('days', 6) }
         }, {
             text: 'Next Week',
             dateStart: function() { return moment().add('weeks', 1).startOf('week') },
             dateEnd: function() { return moment().add('weeks', 1).endOf('week') }
         }],
         applyOnMenuSelect: false,
         datepickerOptions: {
             minDate: null,
             maxDate: null,
            numberOfMonths : 2
         }
     });

    // $(document).on('click','.hide_show_table',function(){
    //     var class_name = $(this).data('id'); 
    //     if( $(this).is(':checked') ){
    //          $(document).find('.' + class_name).show();   
    //     }else{
    //       $(document).find('.' + class_name).hide(); 
    //     }
    // })

});

