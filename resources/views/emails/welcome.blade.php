<!DOCTYPE html>
<html>
<head>
    <title>ESE CRM Email</title>
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
            background-color: #ffffff;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        .content {
            padding: 20px;
            color: #333333;
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
                        <td style="padding: 15px; text-align: center; background-color: #163f7a;border-top-left-radius:5px;border-top-right-radius:5px;">
                            <img src="{{ asset('logo.png') }}" style="height:60px;margin:auto;" />
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            <p>Hello, {{ $name }}</p>
                            <p>{!! $messages !!}</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            <p>&copy; 2024 ESE CRM. All rights reserved.</p>
                            <p>This is an automated email, please do not reply.</p>
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

