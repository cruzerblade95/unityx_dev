@php
$containerFooter =
isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
? 'container-xxl'
: 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                &#169;
                <script>
                document.write(new Date().getFullYear());
                </script>
                , made with ❤️ by <a href="{{ !empty(config('variables.creatorUrl')) ? config('variables.creatorUrl') : '' }}"  class="footer-link">{{ !empty(config('variables.creatorName')) ? config('variables.creatorName') : '' }}</a>
            </div>
            <div class="d-none d-lg-inline-block">
                <a href="{{ config('variables.licenseUrl') ? config('variables.licenseUrl') : '#' }}" class="footer-link me-4" >License</a>
                <a href="{{ config('variables.moreThemes') ? config('variables.moreThemes') : '#' }}"  class="footer-link me-4">More Themes</a>
                <a href="{{ config('variables.documentation') ? config('variables.documentation') . '/laravel-introduction.html' : '#' }}"  class="footer-link me-4">Documentation</a>
                <a href="{{ config('variables.support') ? config('variables.support') : '#' }}"  class="footer-link">Support</a>
            </div>
        </div>
    </div>
</footer>
<!-- / Footer -->
