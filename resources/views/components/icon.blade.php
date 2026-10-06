<svg width="{{ $size ?? 20 }}" height="{{ $size ?? 20 }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>@break
@case('moon')<path d="M20 14a8 8 0 0 1-10-10 8 8 0 1 0 10 10Z"/>@break
@case('search')<circle cx="10.8" cy="10.8" r="6.8"/>
<path d="m16 16 4.5 4.5"/>@break
@case('bag')<path d="M5 7h14l1 14H4L5 7Z"/>
<path d="M8 8V6a4 4 0 0 1 8 0v2"/>@break
@case('user')<circle cx="12" cy="8" r="4"/>
<path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
@case('arrow')<path d="M4 12h16m-6-6 6 6-6 6"/>@break
@case('chevron')<path d="m9 5 7 7-7 7"/>@break
@case('headphones')<path d="M4 13v-2a8 8 0 0 1 16 0v2"/>
<rect x="3" y="11" width="4" height="9" rx="2"/>
<rect x="17" y="11" width="4" height="9" rx="2"/>@break
@case('plug')<path d="M9 2v5m6-5v5M6 7h12v3a6 6 0 0 1-6 6v6m0-6v-2"/>@break
@case('keyboard')<rect x="2" y="6" width="20" height="13" rx="2"/>
<path d="M6 10h1m4 0h1m4 0h1M6 14h1m4 0h1m4 0h1M8 17h8"/>@break
@case('battery')<rect x="3" y="7" width="17" height="10" rx="2"/>
<path d="M22 10v4m-12-4-2 3h4l-2 3"/>@break
@case('truck')<path d="M3 5h11v12H3V5Zm11 4h4l3 4v4h-7"/>
<circle cx="7" cy="18" r="2"/>
<circle cx="18" cy="18" r="2"/>@break
@case('shield')<path d="m12 2 8 3v6c0 5-8 11-8 11S4 16 4 11V5l8-3Z"/>
<path d="m8 11 3 3 5-5"/>@break
@case('return')<path d="m8 3-5 5 5 5M3 8h11a6 6 0 0 1 0 12h-3"/>@break
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@case('minus')<path d="M5 12h14"/>@break
@case('close')<path d="m6 6 12 12M6 18 18 6"/>@break
@case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
@case('pause')<path d="M9 5v14M15 5v14"/>@break
@case('play')<path d="m8 4 12 8-12 8V4Z"/>@break
@case('check')<path d="m5 12 4 4L19 6"/>@break
@case('filter')<path d="M4 7h16M4 17h16"/>
<circle cx="9" cy="7" r="2" fill="white"/>
<circle cx="15" cy="17" r="2" fill="white"/>@break
@endswitch
</svg>
