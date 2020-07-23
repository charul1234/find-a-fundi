<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
</head>
<body style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #f5f8fa; color: #74787E; height: 100%; hyphens: auto; line-height: 1.4; margin: 0; -moz-hyphens: auto; -ms-word-break: break-all; width: 100% !important; -webkit-hyphens: auto; -webkit-text-size-adjust: none; word-break: break-word;">
    <style>
    @media  only screen and (max-width: 600px) {
        .inner-body {
            width: 100% !important;
        }

        .footer {
            width: 100% !important;
        }
    }

    @media  only screen and (max-width: 500px) {
        .button {
            width: 100% !important;
        }
    }
    </style>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #f5f8fa; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
        <tr>
            <td align="center" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                    <tr style="background-color: #383b4e;color:#ffffff;">
                        <td class="header" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 25px 0; text-align: center;">
                            <a href="{{ url('/') }}" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #fff; font-size: 19px; font-weight: bold; text-decoration: none; text-shadow: 0 1px 0 white;">
                                @php $logo = getSetting('logo'); @endphp
                                @if(isset($logo) && $logo!=''  && file_exists(public_path().'/img/'.$logo))
                                    <img src="{{ asset('/img/'.$logo) }}" alt="{{ config('app.name', 'Laravel') }}" height="50">
                                @else
                                    {{ config('app.name', 'Laravel') }}
                                @endif
                            </a>
                        </td>
                    </tr>
                    @yield('content') 
                    <tr>
                        <td style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box;">
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0 auto; padding: 0; text-align: center; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">
                                <tr>
                                    <table width="97%" border="0" align="center" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            @php $facebook_url = getSetting('facebook_url'); @endphp 
                                            @php $instagram_url = getSetting('instagram_url'); @endphp 
                                            @php $linkedin_url = getSetting('linkedin_url'); @endphp 
                                            @php $twitter_url = getSetting('twitter_url'); @endphp 
                                            @if((isset($facebook_url) && $facebook_url!='') || (isset($twitter_url) && $twitter_url!='') || (isset($instagram_url) && $instagram_url!='') || (isset($linkedin_url) && $linkedin_url!=''))
                                                <tr>
                                                    <td colspan="4" style="padding-top:10px;padding-bottom:0px;border-top:1px dashed #e5e5e5">
                                                        <table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="vertical-align:middle;font-size:11px;text-align:center;color:#707070;white-space:nowrap">Follow Us</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center">
                                                        <table width="15%" align="center" border="0" cellspacing="0" cellpadding="0">
                                                            <tbody>
                                                                <tr>
                                                                    @if(isset($facebook_url) && $facebook_url!='')
                                                                    <td align="center" valign="bottom" width="2%" style="padding: 10px 5px;">
                                                                        <em>
                                                                            <a href="{{ $facebook_url }}" style="color:#044e83;text-decoration:none;font-family:Georgia,'Times New Roman',Times,serif;text-decoration:none" target="_blank">
                                                                                <img src="{{asset('/images/facebook.png')}}" alt="" width="36" height="36" style="display:block;border:none;height:auto" border="0" class="CToWUd">
                                                                            </a>
                                                                        </em>
                                                                    </td>
                                                                    @endif
                                                                    @if(isset($instagram_url) && $instagram_url!='')
                                                                    <td align="center" valign="bottom" width="2%" style="padding: 10px 5px;">
                                                                        <em>
                                                                            <a href="{{ $instagram_url }}" style="font-size:22px;color:#044e83;text-decoration:none;text-decoration:none;font-family:Georgia,'Times New Roman',Times,serif" target="_blank">
                                                                                <img src="{{asset('/images/instagram.png')}}" alt="" width="36" height="36" style="display:block;border:none;height:auto" border="0" class="CToWUd">
                                                                            </a>
                                                                        </em>
                                                                    </td>
                                                                    @endif
                                                                     @if(isset($twitter_url) && $twitter_url!='')
                                                                    <td align="center" valign="bottom" width="2%" style="padding: 10px 5px;">
                                                                        <em>
                                                                            <a href="{{ $twitter_url }}" style="font-size:22px;color:#044e83;text-decoration:none;text-decoration:none;font-family:Georgia,'Times New Roman',Times,serif" target="_blank">
                                                                                <img src="{{asset('/images/twitter.png')}}" alt="" width="36" height="36" style="display:block;border:none;height:auto" border="0" class="CToWUd">
                                                                            </a>
                                                                        </em>
                                                                    </td>
                                                                    @endif                                                                    
                                                                    @if(isset($linkedin_url) && $linkedin_url!='')
                                                                    <td align="center" valign="bottom" width="2%" style="padding: 10px 5px;">
                                                                        <em>
                                                                            <a href="{{ $linkedin_url }}" style="font-size:22px;color:#044e83;text-decoration:none;text-decoration:none;font-family:Georgia,'Times New Roman',Times,serif" target="_blank">
                                                                                <img src="{{asset('/images/linkedin.png')}}" alt="" width="36" height="36" style="display:block;border:none;height:auto" border="0" class="CToWUd">
                                                                            </a>
                                                                        </em>
                                                                    </td>
                                                                    @endif                                                                    
                                                                </tr>                        
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endif
                                          
                                        </tbody>
                                    </table>
                                </tr>
                                <tr>
                                    <td class="content-cell" align="center" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;border-top:1px dashed #e5e5e5">
                                        <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; line-height: 1.5em; margin-top: 0; color: #AEAEAE; font-size: 12px; text-align: center;">© {{ date('Y') }} <a target="_blank" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>