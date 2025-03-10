<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Laravel App</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid">
        {{-- <a class="navbar-brand" href="{{ url('/') }}">My App</a> --}}
      </div>
    </nav>

    <div class="container my-4">
       @yield('content')
    </div>

  </body>
</html>
