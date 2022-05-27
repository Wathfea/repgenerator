<!DOCTYPE html>
<html lang="en" class="bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Repgenerator Wizzard</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
</head>
<body>
<div id="repgenrator-wizzard-container">
    <div class="max-w-screen-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-10">
            @if ( isset($steps) )
                <div class="mb-10">
                    @include('repgenerator::steps', $steps)
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
