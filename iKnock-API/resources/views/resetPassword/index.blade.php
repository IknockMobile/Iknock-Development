<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{asset('assets/css/tenant-stylesheet/tenant-style.css')}}">
    <!-- <link rel="stylesheet" href="assets/css/custom.css"> -->
</head>
<body class="bg-img">
    <!-- /.login-logo -->
    <div class="row nomargin">
        <div class="col-md-12" style="text-align:center;">
            <img src="{{asset('image/login-logo.png')}}" class="img-responsive logo-img">
        </div>
    </div>
    <div class="row nomargin">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <div  class="card mt-5">
              @if (count($errors) > 0)
              <div class="row">
                  <div class="col-md-12 col-md-offset-2">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        @foreach($errors->all() as $error)
                        {{ $error }} <br>
                        @endforeach      
                    </div>
                </div>
            </div>
            @endif

            @if (Session::has('message'))
            <div class="alert alert-success" role="alert">
              {{ Session::get('message') }}
          </div>
          @endif

          @if (Session::has('error') && !is_array(Session::get('error')))
          <div class="alert alert-danger" role="alert">
              {{ Session::get('error') }}
          </div>
          @endif

          <form action="{{ route('reset.password.post') }}" method="POST">
                      @csrf
                      <input type="hidden" name="token" value="{{ $token }}">
                      <div class="input-group mb-3">
                          <div class="input-group-prepend">
                            <span class="input-group-text">
                              <i class="fas fa-user"></i>
                          </span>
                      </div>
                      <input class="form-control" type="text" placeholder="{{ __('E-Mail Address') }}" name="email" value="{{ old('email') }}" required autofocus>
                  </div>
                  <div class="input-group mb-4">
                      <div class="input-group-prepend">
                        <span class="input-group-text">
                          <i class="fas fa-key"></i>
                      </span>
                  </div>
                  <input class="form-control" type="password" placeholder="{{ __('Password') }}" name="password" required>
              </div>
              <div class="input-group mb-4">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                       <i class="fas fa-key"></i>
                   </span>
               </div>
               <input class="form-control" type="password" placeholder="{{ __('Password Confirmation') }}" name="password_confirmation" required>
           </div>

           <div class="row">
            <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary"> Reset Password</button>
            </div>
        </div>
        </form>
</div>
</div>
<div class="col-md-4"></div>
</div>
<script src="{{ asset('assets/js/jquery.min.js')}}"></script>
<!-- Bootstrap 3.3.7 -->
<script src="{{ asset('assets/js/bootstrap.js') }}"></script>
<!-- <script src="{{ asset('assets/js/tenant-js/common.js')}}"></script> -->
<!-- iCheck -->
<script src="{{asset('assets/js/tenant-js/login.js')}}"></script>
</body>
</html>
