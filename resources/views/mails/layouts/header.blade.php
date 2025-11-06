<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE-edge">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title> Email </title>
        
        <style>
            * {
                box-sizing: border-box;
                font-family: 'Montserrat', sans-serif !important;
                font-size:14px;
            }
            .outter-container {
                width: 100%;
                background: #f7f7f7;
                padding: 50px 0 100px 0;
            }

            .inner-container {
                width: 600px;
                max-width: 100%;
                margin: 0 auto;
                background: #ffffff;
                border: 1px solid #cccaaa;
            }
            
            .header {
                margin-bottom: 20px;
                margin-top: 15px;
                min-height: 60px;
                padding: 0 15px;
            }

            .float-left {
                float: left !important;
            }

            .clearfix::after {
                content: "";
                clear: both;
                display: table;
            }

            .mail-content {
                padding: 15px 15px 85px 15px;
            }

            .user-info {
                padding: 5px 15px;
            }

            .bg-blue {
                background: rgb(2, 91, 133);
            }

            .text-white {
                color: #ffffff;
            }

            .text-center {
                text-align: center;
            }

            .mail-end {
                margin-top: 25px;
            }

            .margin-0 {
                margin: 0;
            }
            .margin-b-0 {
                margin-bottom: 0;
            }

            .margin-t-5 {
                margin-top: 5px;
            }

            .btn {
                width: 100%;
                padding: 6px 5px;
                text-decoration: none;
                text-align: center;
                font-size: 14px;
                cursor: pointer !important;
                border-radius: 4px;
            }

            .btn.btn-blue {
                border: 1px solid rgb(2, 91, 133);
                background: rgb(2, 91, 133);
                color: #ffffff;
            }

            .btn.btn-blue:hover {
                background: rgb(7, 77, 112);
            }

            .d-block {
                display: block;
            }

            .footer-text {
                text-align: center;
                color: #cacaca;
                margin-top: 15px;
                font-size: 12px;
            }

            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                padding: 15px 0;
            }

            /** Responsive CSS **/
            @media only screen (min-width: 200px) and (max-width: 800px) {
                .inner-container {
                    width: 100%;
                    margin: 0 auto;
                }
            }
        </style>
    </head>

    <body>
        <div class="outter-container">
            <div class="inner-container">
                <div class="header clearfix">
                    <div class="float-left">
                        <img src="http://backoffice.ibimconsulting.com.au/assets/img/logos/logo.png" width="100" alt="logo">
                    </div>
                </div>
            