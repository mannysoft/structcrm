@component('emails.marketing.layout', ['subject' => $step->subject, 'unsubscribeUrl' => $unsubscribeUrl])
    <h1 style="margin:0 0 16px 0; font-size:22px; line-height:28px; color:#111827;">
        Hello {{ $user->name }},
    </h1>

    <div style="font-size:15px; line-height:24px; color:#374151;">
        {!! $bodyHtml !!}
    </div>
@endcomponent
