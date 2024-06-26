</div>
</div>
</div>
<script>
var new_param_url = "{{ url('/') }}/tenant/lead";    
</script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.checkboxes.js') }}"></script>
<!--<script src="https://raw.github.com/berumen/tShift/gh-pages/src/jquery.tshift.min.js"></script>-->
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
<script src="{{ asset('assets/js/jquery.lineProgressbar.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.js"></script>
<script src="{{ asset('assets/js/moment.min.js')}}"></script>
<script src="{{ asset('assets/js/jquery.comiseo.daterangepicker.js')}}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('assets/js/custom.min.js')}}"></script>
<script src="{{ asset('assets/js/tenant-js/common.js')}}"></script>
<script src="{{ asset('assets/js/tenant-js/common_form.js')}}"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/js/bootstrap-select.min.js"></script>
<script src="https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/src/js/bootstrap-datetimepicker.js"> </script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.js" integrity="sha512-am13TYrHJ6yOQ80pSlL4lA5vQrOmSbgLL2pCZXW+NOJrXUWciLP1WH3LCCFJwFkmYYFZw7sVdwwKOFxLLHRUPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- (Optional) Latest compiled and minified JavaScript translation files -->
{{-- <script src="{{ asset('js/colResizable-1.6.js') }}"></script> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css">
<script src="{{ asset('assets/bootstrap-editable.min.js')}}"></script>@include('tenant.include.alert')
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@include('tenant.include.alert')
@yield('script')
<script>
    $("[rel=tooltip]").tooltip({ placement: 'right'});  
    $(document).ready(function () {
            
        $('.datable').DataTable({
               scrollX: true,
               order:[[0,"desc"]]
        });

        $('.datepicker').datepicker({
            format: 'mm/dd/yyyy',
        });
    });
</script>
</body>
</html>
