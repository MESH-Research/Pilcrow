<!DOCTYPE html>
<html>

<head>
  <title>Pilcrow Client</title>
  <meta charset=utf-8>
  <meta name=description content="Client application for pilcrow">
  <meta name=format-detection content="telephone=no">
  <meta name=msapplication-tap-highlight content=no>
  <meta name=viewport content="initial-scale=1,minimum-scale=1,width=device-width">

  <link rel=icon type=image/png href=@cdn_url("logo/app-logo-128x128.png")>
  <link rel=icon type=image/png sizes=16x16 href=@cdn_url("icons/favicon-16x16.png")>
  <link rel=icon type=image/png sizes=32x32 href=@cdn_url("icons/favicon-32x32.png")>
  <link rel=icon type=image/png sizes=96x96 href=@cdn_url("icons/favicon-96x96.png")>
  <link rel=icon type=image/ico href=@cdn_url("favicon.ico")>
  <script type="text/javascript">
    @if (config('app.cdn_base'))
      window.__toCdnUrl =  function (filename) {
           console.log(filename);
            return "{{ config('app.cdn_base') }}/" + filename;
      }
    @else
      window.__toCdnUrl = function (filename) {
        return '/' + filename;
      }
    @endif
  </script>
  @env('local', 'development', 'dev')
    <script type="module" src="/@@vite/client"></script>
    <script type="module" src="/.quasar/client-entry.js"></script>
  @else
    <script type="module" crossorigin src=@cdn_url("assets/index.js")></script>
    <link rel="stylesheet" crossorigin href=@cdn_url("assets/index.css")>
  @endenv
</head>

<body>
  <div id=q-app></div>
</body>


</html>


