<nav aria-label="Progress">
    <ol role="list" class="border border-gray-300 rounded-md divide-y divide-gray-300 md:flex md:divide-y-0">
        @foreach ( $steps as $index => $step )
            @include('repgenerator::step', $step)
        @endforeach
    </ol>
</nav>
