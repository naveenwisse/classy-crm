<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>Appointment - {{ $event->event_name }}</title>
    <style>

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #0087C3;
            text-decoration: none;
        }

        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-size: 12px;
            font-family: 'DejaVu Sans', sans-serif;
        }


        header {
            padding: 5px 0;
            border-bottom: 2px solid #666666;
        }

        #logo img {
            height: 55px;
            margin-bottom: 15px;
        }

        #details {
            margin-bottom: 50px;
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        table th,
        table td {
            padding: 5px 10px 7px 10px;
            text-align: left;
        }

        table th {
            white-space: nowrap;
            font-weight: bold;
            font-size: 1.17em;
        }

        table tfoot td {
            padding: 10px 10px 20px 10px;
            background: #FFFFFF;
            border-bottom: none;
            font-size: 1.2em;
            white-space: nowrap;
            border-bottom: 1px solid #AAAAAA;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr td:first-child {
            border: none;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #AAAAAA;
            padding: 8px 0;
            text-align: center;
        }

        table.billing td {
            background-color: #fff;
        }

        .flex{
            display: block;
        }

        .left{
            float: left; width: 48%;
        }

        .center{
            width: 4%;
        }

        .right{
            float: left; width:48%;
        }

        .logo{
            width: 240px;
        }

        .border table {
            border-collapse: collapse;
        }

        .border td, .border th{
            border: 1px solid #111111;
            padding: 8px;
        }

        /*h3{*/
        /*    font-size: 1.2em;*/
        /*}*/

        .ml-15{
            margin-left: 15px;
        }

        .mr-15{
            margin-right: 15px;
        }

        .font-bold {
            font-weight: bold;
        }

        .notes{
            border: 1px solid #111111; min-height: 100px; margin-left:15px; margin-right:15px; padding: 5px 10px 7px 10px;
        }

        .description{
            border: 1px solid #111111; padding: 5px 10px 7px 10px;
        }
    </style>
</head>
<body>
<header class="clearfix">
    <table cellpadding="0" cellspacing="0" style="margin-bottom: 0px">
        <tr>
            <td width="33%" style="padding-left: 0px;">
                <img src="{{ public_path('/img/worksuite-logo.png') }}" alt="home" class="logo"/>
            </td>
            <td width="33%" style="text-align: center !important;">
                <div id="sheetTitle">
                    <h2>First Appointment Sheet</h2>
                </div>

            </td>
            <td width="33%">
                <div id="sheetTitle">
                </div>
            </td>
        </tr>
    </table>
</header>
<main>
    <div id="details" class="clearfix">
        <table border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div>
                        <h3>Appointment Information:</h3>
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="font-bold">Designer:</td>
                                <td>{{$designer->name}}</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Date:</td>
                                <td>{{date('D, M d, Y', strtotime($event->start_date_time))}}</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Time:</td>
                                <td>{{date('G:i:s A', strtotime($event->start_date_time))}} - {{date('G:i:s A', strtotime($event->end_date_time))}}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <h3>Appointment Description:</h3>
                    <div class="description ml-15 mr-15">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td>{{$event->description}}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        @if($lead->client)
        <table border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div>
                        <h3>Customer Information(Customer ID #{{$lead->client->id}}):</h3>
                        <table border="0" cellspacing="0" cellpadding="0" style="background-color: white">
                            <tr>
                                <td>{{$lead->client->full_name}}</td>
                            </tr>
                            <tr>
                                <td>{{$lead->client->address1}}</td>
                            </tr>
                            @if($lead->client->address2)
                                <tr>
                                    <td>{{$lead->client->address2}}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>{{$lead->client->city}}, {{$lead->client->state }}  {{$lead->client->zip}}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <h3>&nbsp;</h3>
                    <div class="ml-15 mr-15">
                        <table border="0" cellspacing="0" cellpadding="0" style="background-color: white">
                            <tr>
                                <td class="font-bold">Phone:</td>
                                <td>{{$lead->client->phone_number}}</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Cell:</td>
                                <td>{{$lead->client->cell}}</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Email:</td>
                                <td>{{$lead->client->email}}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        @endif
        <table border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div>
                        <h3>Lead Information:(ID #{{$lead->id}}):</h3>
                        <table border="0" cellspacing="0" cellpadding="0" style="background-color: white">
                            <tr>
                                <td>{{$lead->full_name}}</td>
                            </tr>
                            <tr>
                                <td>{{$lead->address1}}</td>
                            </tr>
                            @if($lead->address2)
                                <tr>
                                    <td>{{$lead->address2}}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>{{$lead->city}}, {{$lead->state }}  {{$lead->zip}}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <h3>&nbsp;</h3>
                    <div class="ml-15 mr-15">
                        <table border="0" cellspacing="0" cellpadding="0" style="background-color: white">
                            <tr>
                                <td class="font-bold">Phone:</td>
                                <td>{{$lead->phone}}</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Cell:</td>
                                <td>{{$lead->cell}}</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Email:</td>
                                <td>{{$lead->email}}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        <table border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <h3>Lead Notes:</h3>
                    <div class="notes">
                        @foreach($notes as $note)
                            {{$note->note}} <br/>
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>
        <table border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="border">
                        <h3>Source:</h3>
                        <table border="0" cellspacing="0" cellpadding="0" style="margin-left: 15px">
                            <thead>
                            <tr>
                                <th style="width: 50px;">Sequence</th>
                                <th>Source</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td style="width: 50px;">1</td>
                                <td>{{$lead->lead_source->name}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <h3>Area of Interest:</h3>
                    <div class="ml-15 mr-15 border">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <thead>
                            <tr>
                                <th>Area Name(s)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($interestAreas as $area)
                                <tr>
                                    <td>{{$area->type}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</main>
</body>
</html>