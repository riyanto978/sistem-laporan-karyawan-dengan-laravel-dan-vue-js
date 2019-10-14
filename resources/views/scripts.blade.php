{{-- Global configuration object --}}
@php
$config = [
    'appName' => config('app.name'),
    'locale' => $locale = app()->getLocale(),
    'translations' => json_decode(file_get_contents(resource_path("lang/{$locale}.json")), true),
];
@endphp
<script>
window.config = {!! json_encode($config); !!};
window.tanggal = '{{ date('Y-m-d') }}';
</script>

{{-- Polyfill some features via polyfill.io --}}
@php
// $polyfills = [
//     'Promise',
//     'Object.assign',
//     'Object.values',
//     'Array.prototype.find',
//     'Array.prototype.findIndex',
//     'Array.prototype.includes',
//     'String.prototype.includes',
//     'String.prototype.startsWith',
//     'String.prototype.endsWith',
// ];
@endphp
<script src="{{ asset('public/js/pollifyl.js') }}"></script>

{{-- Load the application scripts --}}
@if (app()->isLocal())
  <script src="{{ asset('public/js/app.js') }}"></script>
@else
  <script src="{{ asset('public/js/manifest.js') }}"></script>
  <script src="{{ asset('public/js/vendor.js') }}"></script>
  <script src="{{ asset('public/js/app.js') }}"></script>
@endif
