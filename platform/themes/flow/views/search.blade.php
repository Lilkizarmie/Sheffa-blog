@php
Theme::layout('homepage');
$description = clean(__('We found :total articles for <strong>":title"</strong> key word', ['total' => $posts->total(), 'title' => app('request')->input('q') ]));
$title = __('Search result');
@endphp

<main>
    {!! Theme::partial('components.headers.layout-2', compact('description', 'title')) !!}
    <div class="container">
        <div class="pt-65 pb-35">
            {!! Theme::partial('loop-list', compact('posts')) !!}
        </div>
    </div>
</main>

