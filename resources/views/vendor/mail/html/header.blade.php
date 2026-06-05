@props(['url'])
<tr>
    <td class="header"
        style="background-color: #3730A3; background-color: oklch(0.3803 0.1386 258.03); padding: 40px; border-top-left-radius: 12px; border-top-right-radius: 12px; text-align: left;">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="left">
                    <a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
                        <img src="{{ asset('logo.png') }}" class="logo" alt="{{ strip_tags($slot) }} Logo"
                            style="vertical-align: middle; margin-right: 8px; display: inline-block; height: 32px; width: auto;"
                            onerror="this.style.display='none'">
                        <span
                            style="vertical-align: middle; font-size: 20px; font-weight: 700; color: #ffffff; letter-spacing: -0.01em;">{!! $slot !!}</span>
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>