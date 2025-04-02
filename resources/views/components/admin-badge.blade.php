@if(Auth::check() && Auth::user()->isAdmin())
    <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="url(#grad1)"
        class="h-6 w-6 inline"
    >
        <defs>
            <linearGradient id="grad1" x1="0%" y1="100%" x2="0%" y2="0%">
                <stop offset="0%" style="stop-color:hsl(190,100%,20%);stop-opacity:1" />
                <stop offset="100%" style="stop-color:#00d4ff;stop-opacity:1" />
            </linearGradient>
        </defs>
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
    </svg>
@endif
