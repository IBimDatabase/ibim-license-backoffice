@extends('mails.layouts.main')
@section('mail-body')
    <div class="bg-blue text-white user-info">
        <p class="margin-b-0"><b>Hi {{@$data['user_name']}}</b></p>
        <p class="margin-t-5">
            We hope this message finds you well.
        </p>
    </div>

    <div class="bg-white mail-content">
        @php
            $daysBeforeExpiry = (int) (@$data['days_before_expiry'] ?? 0);
            $daysText = $daysBeforeExpiry > 0 ? $daysBeforeExpiry . ' day' . ($daysBeforeExpiry > 1 ? 's' : '') : 'a few days';
        @endphp
        <p>
            This is a friendly reminder that your license for the {{(!empty(@$data['purchased_product'])) ? @$data['purchased_product'] : @$data['purchased_package']}} is set to expire on {{@$data['expiry_date']}}, which is just {{ $daysText }} away. To ensure continued access to all features and avoid any service interruptions, we encourage you to renew your license before the expiration date.
        </p>
        <div class="margin-t-2">
            <h3 class="text-bold">License Details:</h3>
            <li>
                <p>
                    <strong>Product/Package:  </strong>{{(!empty(@$data['purchased_product'])) ? @$data['purchased_product'] : @$data['purchased_package']}}
                </p>
            </li>
            <li>
                <p> 
                    <strong>Order Date: </strong>{{(!empty(@$data['order_on'])) ? @$data['order_on'] : '-'}} 
                </p>
            </li>
            <li>
                <p>
                    <strong>Expiry Date: </strong> {{(!empty(@$data['expiry_date'])) ? @$data['expiry_date'] : '-'}}
                </p>
            </li>
        <div>
        
        <p>If you have any questions or need assistance with the renewal process, feel free to contact our support team at info@ibimconsulting.com.au.</p>

        <p>Thank you for choosing IBIM. We appreciate your continued trust in our services.</p>
        <p class="margin-b-0">Best regards,</p>
        <h3 class="margin-t-5">IBIM Consulting</h3>
    </div>
@endsection
