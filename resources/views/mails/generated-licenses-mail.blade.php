@extends('mails.layouts.main')
@section('mail-body')
    <div class="bg-blue text-white user-info">
        <p class="margin-b-0"><b>Dear {{ (@$data['customer_fname']) ? @$data['customer_fname']:@$data['customer_uname'] }},</b></p>
        <p class="margin-t-5">Thank you for purchasing <b>IBim Tools.</b></p>
    </div>

    <div class="bg-white mail-content">
        @if (count(@$data['generatedLicenses']) > 0)
            <p>We appreciate your trust in our products and are confident that our tools will add value to your workflow.</p>
            <p>This email confirms your order and includes the license key(s) for the product(s) you have purchased. We recommend saving this email for future reference, as it contains your subscription and access details.</p>

            <!-- <p>We recommend you save this email so you can easily access your subscription details in the future.</p> -->
            <!-- <p>Kindly go to your account dashboard and click “Download button” to download the application.</p> -->
            <!-- <p>Link : https://ibimconsulting.com.au/my-account</p> -->

            
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
                            <th>Download Link</th>
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
                                <td class="text-center">
                                    @if(!empty(@$license['download_url']))
                                        <a href="{{ @$license['download_url'] }}">{{ @$license['download_url'] }}</a>
                                    @endif
                                </td>
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
                                <td class="text-center">
                                    @if(!empty(@$license['download_url']))
                                        <a href="{{ @$license['download_url'] }}">{{ @$license['download_url'] }}</a>
                                    @endif
                                </td>
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
            <p class="margin-t-10">The package link also includes instructional videos to help you with installation and license activation.</p>
            <p class="margin-t-5">We encourage you to explore the full range of features offered by IBim Tools. If you have any questions or require assistance, please refer to the help documentation or contact our support team.</p>
            <p class="margin-t-5">Thank you once again for choosing <b>IBim Tools</b>. We look forward to supporting your success.</p>

            <p class="margin-b-0">Kind regards,</p>
            <p class="margin-t-0">Sriram Santhanam</p>
            <h3 class="margin-t-5">IBim Consulting</h3>
        @endif
    </div>
@endsection