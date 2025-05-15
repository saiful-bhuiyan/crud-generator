@extends('admin.layouts.master')
@section('body')

<div class="content">
<div class="page-header">
    <div class="page-title">
        <h4>General Setting</h4>
        <h6>Manage General Setting</h6>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('general-settings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Site Title <span class="manitory">*</span></label>
                        <input type="hidden" name="types[]" value="site_title">
                        <input type="text" name="site_title" value="{{ getGeneralSetting('site_title') }}" placeholder="Enter Title">
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label> Site Logo</label>
                        <div class="image-upload">
                            <input type="hidden" name="types[]" value="site_logo">
                            <input type="file" name="site_logo">
                            <div class="image-uploads">
                                <img src="{{ getGeneralSetting('site_logo') ? getGeneralSetting('site_logo') : static_asset('assets/img/icons/upload.svg') }}" height="70px" width="70px" alt="img">
                                <h4>Drag and drop a file to upload</h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Currency <span class="manitory">*</span></label>
                        <input type="hidden" name="types[]" value="currency">
                        <input type="text" name="currency" value="{{ getGeneralSetting('currency') }}" placeholder="Enter Currency">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Currency Decimal<span class="manitory">*</span></label>
                        <input type="hidden" name="types[]" value="currency_decimal">
                        <input type="text" name="currency_decimal" value="{{ getGeneralSetting('currency_decimal') }}" placeholder="Enter Decimal">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Date Format<span class="manitory">*</span></label>
                        <input type="hidden" name="types[]" value="date_format">
                        <select class="select" name="date_format">
                            <option value="">Choose Date Format </option>
                            <option value="DD/MM/YYYY" {{ getGeneralSetting('date_format') == 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            <option value="MM/DD/YYYY" {{ getGeneralSetting('date_format') == 'MM/DD/YYYY' ? 'selected' : '' }}>MM/DD/YYYY</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Email<span class="manitory">*</span></label>
                        <input type="hidden" name="types[]" value="email">
                        <input type="text" name="email" value="{{ getGeneralSetting('email') }}" placeholder="Enter email">
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Phone<span class="manitory">*</span></label>
                        <input type="hidden" name="types[]" value="phone">
                        <input type="text" name="phone" value="{{ getGeneralSetting('phone') }}" placeholder="Enter Phone">
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="form-group">
                        <label>Address<span class="manitory">*</span> </label>
                        <input type="hidden" name="types[]" value="address">
                        <input type="text" name="address" value="{{ getGeneralSetting('address') }}" placeholder="Enter Address">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ url()->previous() }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

</div>

@endsection