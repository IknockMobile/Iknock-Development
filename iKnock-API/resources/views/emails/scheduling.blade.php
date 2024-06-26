<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <style type="text/css">
            /* FONTS */
            @media screen {
                @font-face {
                    font-family: 'Lato';
                    font-style: normal;
                    font-weight: 400;
                    src: local('Lato Regular'), local('Lato-Regular'), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format('woff');
                }

                @font-face {
                    font-family: 'Lato';
                    font-style: normal;
                    font-weight: 700;
                    src: local('Lato Bold'), local('Lato-Bold'), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format('woff');
                }

                @font-face {
                    font-family: 'Lato';
                    font-style: italic;
                    font-weight: 400;
                    src: local('Lato Italic'), local('Lato-Italic'), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format('woff');
                }

                @font-face {
                    font-family: 'Lato';
                    font-style: italic;
                    font-weight: 700;
                    src: local('Lato Bold Italic'), local('Lato-BoldItalic'), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format('woff');
                }
            }

            /* CLIENT-SPECIFIC STYLES */
            body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
            table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
            img { -ms-interpolation-mode: bicubic; }

            /* RESET STYLES */
            img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
            table { border-collapse: collapse !important; }
            body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }

            /* iOS BLUE LINKS */
            a[x-apple-data-detectors] {
                color: inherit !important;
                text-decoration: none !important;
                font-size: inherit !important;
                font-family: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
            }

            .text-head{
                text-decoration: none;
                font-weight: bold;
                color: #fff;
            }

            .text-head h1{
                color: #fff;
                font-size: 35px;
            }

            /* ANDROID CENTER FIX */
            div[style*="margin: 16px 0;"] { margin: 0 !important; }
        </style>
    </head>
    <body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <!-- LOGO -->
            <tr>
                <td bgcolor="#1E3062" align="center">
                    <table border="0" cellpadding="0" cellspacing="0" width="480" >
                        <tr>
                            <td align="center" valign="top" style="padding: 40px 10px 40px 10px;">
                                <a clicktracking="off" href="{{ env('APP_URL') }}" target="_blank" class="text-head">
                                    <h1>iKnock</h1>
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- HERO -->
            <tr>
                <td bgcolor="#1E3062" align="center" style="padding: 0px 10px 0px 10px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="480" >
                        <tr>
                            <td bgcolor="#ffffff" align="center" valign="top" style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                <h1 style="font-size: 32px; font-weight: 400; margin: 0;">iKnock New Appointment Scheduled</h1>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="480" >
                        <tr>
                            <td bgcolor="#ffffff" align="left" style="padding: 20px 30px 40px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;" >
                                <p>Hi {{ $data['name'] }},</p>
                                <p>
                                    A new appointment has been scheduled in <strong>iKnock</strong>.                                    
                                </p>
                                <p>
                                    {{ date('m-d-Y g:i a',strtotime($data['start'])) }}
                                </p>
                                <?php if ($data['scheduled_user'] != '') { ?>
                                    <p>
                                        User who scheduled the appointment:  {{ $data['scheduled_user'] }}
                                    </p>
                                <?php } ?>
                                <?php if ($data['homeowner_name'] != '') { ?>
                                    <p>
                                        Homeowner Name: : {{ $data['homeowner_name'] }}
                                    </p>
                                <?php } ?>
                                <?php if ($data['address'] != '') { ?>
                                    <p>
                                        {{$data['address']}}
                                    </p>
                                <?php } ?>
                                <?php if ($data['person_meeting'] != '') { ?>
                                    <p>
                                        Person With Whom You Are Meeting: {{$data['person_meeting']}}
                                    </p>
                                <?php } ?>
                                <?php if ($data['phone'] != '') { ?>
                                    <p>
                                        Phone: {{$data['phone']}}
                                    </p>
                                <?php } ?>
                                <?php if ($data['email'] != '') { ?>
                                    <p>
                                        E mail: {{$data['email']}}
                                    </p>
                                <?php } ?>
                                <?php if ($data['additional_notes'] != '') { ?>
                                    <p>
                                        Additional notes: {{$data['additional_notes']}}
                                    </p>
                                <?php } ?>
                                <?php if ($data['note'] != '') { ?>
                                    <p>
                                        Notes: {{$data['note']}}
                                    </p>
                                <?php } ?>
                            </td>
                        </tr>              
                    </table>
                </td>
            </tr>
        </table>

    </body>
</html>
