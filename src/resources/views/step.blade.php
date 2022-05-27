@if ( $complete )
    <li class="relative md:flex-1 md:flex">
    <!-- Completed Step -->
    <a href="{{ route('repwizz.step', ['step' => $number]) . $query }}" class="group flex items-center w-full">
    <span class="px-6 py-4 flex items-center text-sm font-medium">
      <span class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-indigo-600 rounded-full group-hover:bg-indigo-800">
        <!-- Heroicon name: solid/check -->
        <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
      </span>
      <span class="ml-4 text-sm font-medium text-gray-900">{{ $title }}</span>
    </span>
    </a>

    <!-- Arrow separator for lg screens and up -->
    <div class="hidden md:block absolute top-0 right-0 h-full w-5" aria-hidden="true">
        <svg class="h-full w-full text-gray-300" viewBox="0 0 22 80" fill="none" preserveAspectRatio="none">
            <path d="M0 -2L20 40L0 82" vector-effect="non-scaling-stroke" stroke="currentcolor" stroke-linejoin="round" />
        </svg>
    </div>
</li>
@elseif ( $current )
    <li class="relative md:flex-1 md:flex">
    <!-- Current Step -->
    <a href="#" class="px-6 py-4 flex items-center text-sm font-medium" aria-current="step">
    <span class="flex-shrink-0 w-10 h-10 flex items-center justify-center border-2 border-indigo-600 rounded-full">
      <span class="text-indigo-600">{{ $index }}</span>
    </span>
        <span class="ml-4 text-sm font-medium text-indigo-600">{{ $title  }}</span>
    </a>
    @if ( count($steps) > $index )
        <!-- Arrow separator for lg screens and up -->
        <div class="hidden md:block absolute top-0 right-0 h-full w-5" aria-hidden="true">
            <svg class="h-full w-full text-gray-300" viewBox="0 0 22 80" fill="none" preserveAspectRatio="none">
                <path d="M0 -2L20 40L0 82" vector-effect="non-scaling-stroke" stroke="currentcolor" stroke-linejoin="round" />
            </svg>
        </div>
    @endif
</li>
@else
    <li class="relative md:flex-1 md:flex">
    <!-- Upcoming Step -->
    <a href="#" class="group flex items-center">
    <span class="px-6 py-4 flex items-center text-sm font-medium">
      <span class="flex-shrink-0 w-10 h-10 flex items-center justify-center border-2 border-gray-300 rounded-full group-hover:border-gray-400">
        <span class="text-gray-500 group-hover:text-gray-900">{{ $index }}</span>
      </span>
      <span class="ml-4 text-sm font-medium text-gray-500 group-hover:text-gray-900">{{ $title }}</span>
    </span>
    </a>
    @if ( count($steps) > $index )
        <!-- Arrow separator for lg screens and up -->
        <div class="hidden md:block absolute top-0 right-0 h-full w-5" aria-hidden="true">
            <svg class="h-full w-full text-gray-300" viewBox="0 0 22 80" fill="none" preserveAspectRatio="none">
                <path d="M0 -2L20 40L0 82" vector-effect="non-scaling-stroke" stroke="currentcolor" stroke-linejoin="round" />
            </svg>
        </div>
    @endif
</li>
@endif


