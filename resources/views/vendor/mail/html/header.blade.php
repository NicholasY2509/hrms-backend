@props(['url'])
<tr>
<td class="header">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
{{-- Put your logo file in the "public" folder and name it "logo.png" --}}
<img src="{{ asset('logo.png') }}" class="logo" alt="{{ strip_tags($slot) }} Logo" style="vertical-align: middle; margin-right: 8px; display: inline-block;" onerror="this.style.display='none'">
<span style="vertical-align: middle;">{!! $slot !!}</span>
</a>
</td>
</tr>
</table>
</td>
</tr>
