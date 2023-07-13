@extends('admin.layout.base')
@extends('admin.layout.base2')
@section('title', 'Add User ')

@section('content')

<div class="content-wrapper">
	@include('admin.include.flashmsg')
	<div class="container-fluid">
		<div class="box box-block bg-white">
			<a href="{{ route('admin.driver.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">Add Driver</h5>
			
			<form class="form-horizontal" action="{{route('admin.driver.update', $user->id)}}" method="POST" enctype="multipart/form-data" role="form">
				{{csrf_field()}}
              
               
            
				<div class="form-group row">
					<label for="first_name" class="col-xs-12 col-form-label">First Name</label>
					<div class="col-xs-10">
						<input class="form-control" onkeydown="return /[a-z]/i.test(event.key)" type="text" value="{{ $user->first_name }}" name="first_name" required id="first_name" placeholder="First Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="last_name" class="col-xs-12 col-form-label">Last Name</label>
					<div class="col-xs-10">
						<input class="form-control" onkeydown="return /[a-z]/i.test(event.key)" type="text" value="{{ $user->last_name }}" name="last_name" required id="last_name" placeholder="Last Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="age" class="col-xs-12 col-form-label">Age</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" min="18" value="{{ $user->age }}" name="age" required id="age" placeholder="Age">
					</div>
				</div>

				<div class="form-group row">
					<label for="dob" class="col-xs-12 col-form-label">Date Of Birth</label>
					<div class="col-xs-10">
						<input class="form-control" type="date" value="{{ $user->dob }}" name="dob" required id="dob" placeholder="date of birth">
					</div>
				</div>

				<div class="form-group row">
					<label for="driving_license" class="col-xs-12 col-form-label">Driving License</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->drivinglicense }}" name="drivinglicense" required id="drivinglicense" placeholder="Driving License">
					</div>
				</div>

				<div class="form-group row">
					<label for="drivinglicenseDateOfExp" class="col-xs-12 col-form-label">Driving License Date of Expiration</label>
					<div class="col-xs-10">
						<input class="form-control" type="date" value="{{ $user->dateofexpiration}}" name="dateofexpiration" required id="dateofexpiration" placeholder="Driving License Date of Expiration">
					</div>
				</div>
				<!--  -->
				<div class="form-group row">
					<div class="col-xs-5">
                        
                        @if (isset($licensefront))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$licensefront}}" alt="Adharfront">
                    @endif
						<label for="licensePicOne" class="col-xs-12 col-form-label">License Pic One</label>
						<input type="file" accept="image/*" name="licensePicOne" class="dropify form-control-file" id="licensePicOne" aria-describedby="fileHelp">
					</div>
					<div class="col-xs-5">
                        @if (isset($licenseback))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$licenseback}}" alt="Adharfront">
                    @endif
						<label for="licensePicTwo" class="col-xs-12 col-form-label">License Pic Two</label>
						<input type="file" accept="image/*" name="licensePicTwo" class="dropify form-control-file" id="licensePicTwo" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="email" class="col-xs-12 col-form-label">Email</label>
					<div class="col-xs-10">
						<input class="form-control" type="email" required name="email" value="{{$user->email}}" id="email" placeholder="Email">
					</div>
				</div>

				<div class="form-group row">
				@if (isset($user->picture))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public{{$user->picture}}" alt="Adharfront">
                    @endif
					<label for="picture" class="col-xs-12 col-form-label">Profile Picture</label>
					
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="picture" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="adharnumber" class="col-xs-12 col-form-label">Aadhaar Card Number</label>
					<div class="col-xs-10">
						<input class="form-control" type="Number" value="{{ $user->adharnumber }}" name="adharnumber" required id="adharnumber" placeholder="Aadhaar Card Number">
					</div>
				</div>

				<div class="form-group row">
					<div class="col-xs-5">
                        @if (isset($adharfront))
                        <img style="height: 50px; border-radius:2em;" src="{{  URL::to('/') }}/ycab/ypc4/public/uploads/driver/{{$adharfront}}" alt="Adharfront">
                    @endif
						<label for="aadhaarPicOne" class="col-xs-12 col-form-label">Aadhaar Card Pic One</label>
						<input type="file" accept="image/*" name="aadhaarPicOne" class="dropify form-control-file" id="aadhaarPicOne" aria-describedby="fileHelp">
					</div>
				
					<div class="col-xs-5">
                       
                        @if (isset($adharback))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$adharback}}" alt="Adharfront">
                    @endif
						<label for="aadhaarPicTwo" class="col-xs-12 col-form-label">Aadhaar Card Pic Two</label>
						<input type="file" accept="image/*" name="aadhaarPicTwo" class="dropify form-control-file" id="aadhaarPicTwo" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
                    @if (isset($policeclear))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$policeclear}}" alt="Adharfront">
                    @endif
					<label for="puc" class="col-xs-12 col-form-label">Police Clearance Certificate</label>
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="puc" class="dropify form-control-file" id="puc" aria-describedby="fileHelp">
					</div>
				</div>

				<!-- <div class="form-group row">
					<label for="information" class="col-xs-12 col-form-label">Information</label>
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="information" class="dropify form-control-file" id="information" aria-describedby="fileHelp">
					</div>
				</div> -->

				<div class="form-group row">
					<label for="vehiclecategory_id" class="col-xs-12 col-form-label">Vehicle Category</label>
					<div class="col-xs-10">
						<select class="form-control" value="{{ $user->vehiclecategory_id}}" name="vehiclecategory_id" required id="vehiclecategory_id" placeholder="Vehicle Category">
							
							@foreach ($category as $cat)
							<option value="{{ $cat->id }}" {{ $cat->id == $user->vehiclecategory_id ? 'selected' : '' }}>{{ $cat->name}}</option>
						@endforeach
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="vehiclebrand_id" class="col-xs-12 col-form-label">Vehicle Brand</label>
					<div class="col-xs-10">
						<select class="form-control" value="{{ $user->vehiclebrand_id }}" name="vehiclebrand_id" required id="vehiclebrand_id" placeholder="Vehicle Brand">
							
							@foreach ($brand as $brands)
                            <option value="{{ $brands->id }}" {{ $brands->id == $user->vehiclebrand_id ? 'selected' : '' }}>{{ $brands->name}}</option>
						@endforeach
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="vehiclemodel_id" class="col-xs-12 col-form-label">Vehicle Model</label>
					<div class="col-xs-10">
						<select class="form-control" value="{{ $user->vehiclemodel_id }}" name="vehiclemodel_id" required id="vehiclemodel_id" placeholder="Vehicle Model">
							
							@foreach ($model as $models)
							<option value="{{ $models->id }}" {{ $models->id == $user->vehiclemodel_id ? 'selected' : '' }}>{{ $models->name}}</option>
						@endforeach
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="vehicleyear" class="col-xs-12 col-form-label">Vehicle Year</label>
					<div class="col-xs-10">
						<input type="date" name="vehicleyear" class="form-control" id="vehicleyear" value="{{ $user->vehicleyear }}" required>
					</div>
				</div>

				<div class="form-group row">
					<label for="vehiclecolor_id" class="col-xs-12 col-form-label">Vehicle Color</label>
					<div class="col-xs-10">
						<select class="form-control" value="{{ $user->vehiclecolor_id }}" name="vehiclecolor_id" required id="vehiclecolor_id" placeholder="Vehicle Color">
							
							@foreach ($color as $colors)
							<option value="{{ $colors->id }}" {{ $colors->id == $user->vehiclecolor_id ? 'selected' : '' }}>{{ $colors->name}}</option>
                           
						@endforeach
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="vehiclenumberplate" class="col-xs-12 col-form-label">Vehicle Number Plate</label>
					<div class="col-xs-10">
						<input type="text" name="vehiclenumberplate"value="{{ $user->vehiclenumberplate}}" class="form-control" id="vehiclenumberplate" placeholder="Vehicle Number Plate" required>
					</div>
				</div>
				
				@if (isset($vehiclenumberplatephoto))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$vehiclenumberplatephoto}}" alt="Adharfront">
                    @endif
				<div class="form-group row">
					<label for="vehiclenumberplatephoto" class="col-xs-12 col-form-label">Vehicle Photo with Number Plate</label>
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="vehiclenumberplatephoto" class="dropify form-control-file" id="vehiclenumberplatephoto" aria-describedby="fileHelp">
					</div>
				</div>
				
				@if (isset($registercertifront))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$registercertifront}}" alt="Adharfront">
                    @endif
				<div class="form-group row">
					<div class="col-xs-5">
						<label for="registercertifront" class="col-xs-12 col-form-label">Registration Photo Front</label>
						<input type="file" accept="image/*" name="registercertifront" class="dropify form-control-file" id="registercertifront" aria-describedby="fileHelp">
					</div>
					@if (isset($registercertiback))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$registercertiback}}" alt="Adharfront">
                    @endif
					<div class="col-xs-5">
						<label for="registercertiback" class="col-xs-12 col-form-label">Registration Photo back</label>
						<input type="file" accept="image/*" name="registercertiback" class="dropify form-control-file" id="registercertiback" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					@if (isset($permitcommercialcerti))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$permitcommercialcerti}}" alt="Adharfront">
                    @endif
					<div class="col-xs-5">
						<label for="permitcommercialcerti" class="col-xs-12 col-form-label">Commercial Certificate Photo One</label>
						<input type="file" accept="image/*" name="permitcommercialcerti" class="dropify form-control-file" id="permitcommercialcerti" aria-describedby="fileHelp">
					</div>
					@if (isset($permitcommercialcertitwo))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$permitcommercialcertitwo}}" alt="Adharfront">
                    @endif
					<div class="col-xs-5">
						<label for="permitcommercialcertitwo" class="col-xs-12 col-form-label">Commercial Certificate Photo Two</label>
						<input type="file" accept="image/*" name="permitcommercialcertitwo" class="dropify form-control-file" id="permitcommercialcertitwo" aria-describedby="fileHelp">
					</div>
					@if (isset($permitcommercialcertithree))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$permitcommercialcertithree}}" alt="Adharfront">
                    @endif
					<div class="col-xs-5">
						<label for="permitcommercialcertithree" class="col-xs-12 col-form-label">Commercial Certificate Photo Three</label>
						<input type="file" accept="image/*" name="permitcommercialcertithree" class="dropify form-control-file" id="permitcommercialcertithree" aria-describedby="fileHelp">
					</div>
				</div>
				@if (isset($vehicleinsurance))
                        <img style="height: 50px; border-radius:2em;" src="{{URL::to('/')}}/ycab/ypc4/public/uploads/driver/{{$vehicleinsurance}}" alt="Adharfront">
                    @endif
				
				<div class="form-group row">
					<label for="vehicleinsurance" class="col-xs-12 col-form-label">Vehicle Insurance Photo</label>
					<div class="col-xs-12">
						<input type="file" accept="image/*" name="vehicleinsurance" class="dropify form-control-file" id="vehicleinsurance">
					</div>
				</div>

				
					

				<div class="form-group row">
					<label for="referalcode" class="col-xs-12 col-form-label">Referral Code</label>
					<div class="col-xs-12">
						<input type="text" name="referalcode" class="form-control" id="referalcode" value="{{$user->referalcode }}" required>
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-12 col-form-label">Mobile</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->mobile }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>

				<div class="form-group row">
					<label for="wallet_balance" class="col-xs-12 col-form-label">Wallet</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ $user->wallet_balance}}" name="wallet_balance" required id="wallet_balance" placeholder="wallet_balance">
					</div>
				</div>
				<input type="hidden" name="role_id" value="8">

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Add User</button>
						<a href="{{route('admin.driver.index')}}" class="btn btn-default">Cancel</a>
					</div>
				</div>
                        
               
			</form>
		</div>
	</div>
</div>

@endsection