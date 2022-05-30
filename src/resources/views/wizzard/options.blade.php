<div class="mt-3">
    @foreach( $options as $option => $description )
        <div class="mt-3 text-sm">
            <div class="relative flex items-start">
                <div class="flex items-center h-5">
                    <input name="{{ strtolower($option)  }}" {{  session()->get(strtolower($option), false) ? 'checked' : '' }} type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label class="font-medium text-gray-700">{{ $option }}</label>
                    <p class="text-gray-500">{{ $description }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
