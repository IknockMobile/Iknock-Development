@include('tenant.include.header')
@include('tenant.include.sidebar')

<div class="right_col" role="main">
	<div class="row" id="content-heading">
        <!--content-heading here-->
        <div class="col-md-9">
            <h1 class="cust-head">Mail-chimp Campaign Content</h1>
        </div>
   </div>
   <div class="row">
	   	<div class="col-md-12">
	   		<div class="panel panel-default">
	   			<div class="panel-heading">
   					<h5>Mail-chimp Campaign Content</h5>
   				</div>
   				<div class="panel-body">
   					{!! $marketingListMail !!}
   				</div>
	   		</div>	
	   	</div>
   </div>
</div>	
@include('tenant.include.footer')
