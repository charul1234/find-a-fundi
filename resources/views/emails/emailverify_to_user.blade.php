@extends('emails.common')
@section('content')
<tr>
    <td class="body" width="100%" cellpadding="0" cellspacing="0" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #FFFFFF; border-bottom: 1px solid #EDEFF2; border-top: 1px solid #EDEFF2; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; background-color: #FFFFFF; margin: 0 auto; padding: 0; width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px;">
            <!-- Body content -->
            <tr>
                <td class="content-cell" style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                    <h1 style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 19px; font-weight: bold; margin-top: 0; text-align: left;">Hello, {{ ucfirst($receiver) }}!</h1>
                    <p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787E; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">Thanks for subscribing our newsletters.</p>
                </td>
            </tr>
        </table>
    </td>
</tr>
@endsection
