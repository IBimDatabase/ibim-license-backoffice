@extends('mails.layouts.main')
@section('mail-body')
    <div class="bg-blue text-white user-info">
        <p class="margin-b-0"><b>Hi {{ (@$data['customer_fname']) ? @$data['customer_fname']:@$data['customer_uname'] }},</b></p>
        <p class="margin-t-5">Thank you for your purchase!</p>
    </div>

    <div class="bg-white mail-content">
        @if (count(@$data['generatedLicenses']) > 0)
            <p>This email confirms your order of the below license key(s).</p>

            <p>We recommend you save this email so you can easily access your subscription details in the future.</p>
            <p>Kindly go to your account dashboard and click “Download button” to download the application.</p>
            <p>Link : https://ibimconsulting.com.au/my-account</p>

            
            <p>In order to use some of the products in this order you will require the following license key(s). </p>

            <div class="table-responsive">
                <table border="1" cellpadding="5" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Sno</th>
                            @if(@$data['generatedLicenses'][0]['type'] == 'PACKAGE')
                            <th>Package Name</th>
                            @else
                            <th>Product Name</th>
                            @endif
                            <th>License Type</th>
                            <th>License Key</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($data['generatedLicenses'] as $license)
                        @if($license['type'] == 'PRODUCT')
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">{{ @$license['details']['product']['product_name'] }}</td>
                                <td class="text-center">{{ ucwords(strtolower(str_replace('_', ' ', @$license['details']['license_type']))) }}</td>
                                <td class="text-center">{{ @$license['details']['license_key'] }}</td>
                            </tr>
                        @elseif($license['type'] == 'PACKAGE')
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">{{ @$license['details'][0]['package']['package_name']}}
                                {{-- @foreach($license['details'] as $details)
                                    {{@$details['product']['product_name']}}
                                @endforeach --}}
                                </td>
                                <td class="text-center">{{ ucwords(strtolower(str_replace('_', ' ', @$license['details'][0]['license_type']))) }}</td>
                                <td class="text-center">{{ @$license['details'][0]['license_key'] }}</td>
                            </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(@$data['generatedLicenses'][0]['type'] == 'PACKAGE')
                <p>This package contains following product(s):</p>
                @foreach(@$data['generatedLicenses'][0]['details'] as $details)
                    - {{@$details['product']['product_name']}} <br />
                @endforeach
            @endif
            
            <p class="margin-b-0">Thank You.</p>
            <h3 class="margin-t-5">IBIM Consulting</h3>
        @endif
    </div>
@endsection
