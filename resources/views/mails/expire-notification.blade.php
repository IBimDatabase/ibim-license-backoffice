@extends('mails.layouts.main')
@section('mail-body')
    <div class="bg-blue text-white user-info">
        <p class="margin-b-0"><b>Hi {{@$data['user_name']}}</b></p>
        <p class="margin-t-5">
            We hope you are well.
        </p>
        
    </div>

    <div class="bg-white mail-content">
        <p>Attached, please find your monthly report generated from IBIM Backoffice, summarizing the account details and purchased data for <strong>{{@$data['past_from_month']}}</strong> month. Additionally, we have included a separate attachment highlighting the licenses due to expire in the upcoming month.</p>

        <div class="margin-t-2">
            <h3 class="text-bold">Key Details:</h3>
            <li>
                <p>
                    <strong>Account Overview: </strong>Username, Email, Mobile
                </p>
            </li>
            <li>
                <p> 
                    <strong>Purchased Tools/Packages: </strong>Detailed in the attached report 
                </p>
            </li>
            <li>
                <p>
                    <strong>Upcoming License Expirations: </strong> Refer to the attached expiry list
                </p>
            </li>
        <div>
        
        <p>Please review the documents and let us know if you have any questions or need further assistance. We appreciate your continued trust in our services.</p>

        <p>Thank You</p>
        <p class="margin-b-0">Warm regards,</p>
        <h3 class="margin-t-5">IBIM Consulting</h3>
    </div>
@endsection
