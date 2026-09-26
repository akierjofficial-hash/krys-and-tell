@props(['title', 'description' => null, 'parent' => null, 'parentUrl' => null])
<header class="admin-page-header">
    <div>
        @if($parent)
            <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                @if($parentUrl)<a href="{{ $parentUrl }}">{{ $parent }}</a>@else<span>{{ $parent }}</span>@endif
                <span aria-hidden="true"> / </span><span aria-current="page">{{ $title }}</span>
            </nav>
        @endif
        <h1 class="admin-page-title">{{ $title }}</h1>
        @if($description)<p class="admin-page-description">{{ $description }}</p>@endif
    </div>
    @if(trim($actions ?? ''))<div class="d-flex gap-2 flex-wrap">{{ $actions }}</div>@endif
</header>
