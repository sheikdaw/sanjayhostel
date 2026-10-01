{{-- Usage: @include('partials.faq', ['faqs' => [[question, answer], ...], 'faqId' => 'alandur-faq'])
     Markup matches the existing accordion (handled by the layout script). --}}
<div class="faq-list">
    @foreach ($faqs as $faq)
        <div class="faq-item">
            <button class="faq-q" type="button" aria-expanded="false">{{ $faq[0] }}<span class="plus" aria-hidden="true">+</span></button>
            <div class="faq-a">{!! $faq[1] !!}</div>
        </div>
    @endforeach
</div>
