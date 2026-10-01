{{-- Usage: @include('partials.breadcrumbs', ['crumbs' => [['Home','/'], ['Our hostels','/hostels'], ['Alandur','/hostels/alandur']]])
     The last item is the current page (plain text). Visible trail + BreadcrumbList JSON-LD. --}}
@php($currentPath = end($crumbs)[1])
<nav class="crumbs" aria-label="Breadcrumb">
    <ol>
        @foreach ($crumbs as $i => [$label, $path])
            @if ($loop->last)
                <li aria-current="page">{{ $label }}</li>
            @else
                <li><a href="{{ $path }}">{{ $label }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
{!! \App\Support\Seo::json([\App\Support\Seo::breadcrumbs($crumbs, $currentPath)]) !!}
