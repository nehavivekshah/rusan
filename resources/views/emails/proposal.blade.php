<!DOCTYPE html>
<html>
<head>
    <title>Business Proposal</title>
    <style>
        /* General Reset */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        table {
            border-spacing: 0;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f4f4f4;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        .content {
            padding: 20px;
            color: #333333;
            background-color: #ffffff;
        }
        .footer {
            padding: 10px 20px;
            text-align: center;
            font-size: 12px;
            color: #999999;
            background-color: #f4f4f4;
            border-bottom-left-radius:5px;
            border-bottom-right-radius:5px;
        }
        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            .content {
                padding: 15px;
            }
            .footer {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>
                <table>
                    <tr>
                        <td class="content">
                            <p>Hello, {{ $name }}</p>
                            <p>{!! $messages !!}</p>
                            <p>{!! $signature !!}</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            <p>{{ $company ?? '' }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @if(isset($trackingToken))
        <img src="{{ route('email.track_open', ['token' => $trackingToken]) }}" width="1" height="1" style="display:none;" />
    @endif
</body>
</html>

