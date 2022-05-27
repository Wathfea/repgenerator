@extends('repgenerator::layout')

@section('content')
    <form class="space-y-8 divide-y divide-gray-200" method="{{ $method }}"  action="{{ $route }}">
        @if( in_array($step,[1,3]) )
            <div>
                @include('repgenerator-wizzard::repository')
            </div>
        @endif
        @if( in_array($step,[2,3]) )
            <div>
                @include('repgenerator-wizzard::migration')
            </div>
        @endif

        @include('repgenerator-wizzard::options', $options)

        <div class="pt-5">
            <div class="flex justify-end">
                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ $step == 3 ? 'Finish' : 'Next' }}
                </button>
            </div>
        </div>
    </form>
@endsection
