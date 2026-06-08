@php $width = $width ?? '28'; @endphp

<svg width="{{ $width }}" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Left page -->
    <path d="M18 7 C13.5 5.5 8 5.5 4 8 L4 29 C8 26.5 13.5 26.5 18 28 L18 7Z"
          fill="currentColor"/>
    <!-- Right page (lighter) -->
    <path d="M18 7 C22.5 5.5 28 5.5 32 8 L32 29 C28 26.5 22.5 26.5 18 28 L18 7Z"
          fill="currentColor" opacity="0.35"/>
    <!-- Spine -->
    <line x1="18" y1="7" x2="18" y2="28"
          stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
    <!-- Lines on left page -->
    <line x1="7.5" y1="14" x2="15" y2="13"  stroke="white" stroke-width="1.4" stroke-linecap="round" opacity="0.75"/>
    <line x1="7.5" y1="18.5" x2="15" y2="17.5" stroke="white" stroke-width="1.4" stroke-linecap="round" opacity="0.75"/>
    <line x1="7.5" y1="23" x2="15" y2="22"  stroke="white" stroke-width="1.4" stroke-linecap="round" opacity="0.75"/>
    <!-- Star accent top-right -->
    <path d="M27 5 L27.6 7 L29.5 7 L28 8.2 L28.6 10.2 L27 9 L25.4 10.2 L26 8.2 L24.5 7 L26.4 7 Z"
          fill="currentColor" opacity="0.7"/>
</svg>
