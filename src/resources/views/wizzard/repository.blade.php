<div class="space-y-8 divide-y divide-gray-200">
    <div>
        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-12">
                <label for="first-name" class="block text-sm font-medium text-gray-700">
                    Name (Singular - Ex. Dog )
                </label>
                <div class="mt-1">
                    <input type="text" required name="name" value="{{ session()->get('name', '') }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
            </div>
        </div>
    </div>
</div>
