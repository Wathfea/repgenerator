<script>
    window.addColumn = function() {
        let columnsContainer = document.getElementById('repgenerator-wizzard-columns');
        if ( columnsContainer ) {
            let firstChild = columnsContainer.firstElementChild;
            if ( firstChild ) {
                let clonedColumn = firstChild.cloneNode(true);
                if ( clonedColumn ) {
                    let loopCounter =  parseInt(document.getElementById('loop-counter').value);
                    let regex = /[0]/i;
                    let checkboxClassNames = ['.columnNulls','.columnAutoIncrements','.columnUnsigneds','.columnCascades','.columnSearchables', '.columnIndexes', '.columnPrecisions'];

                    checkboxClassNames.forEach(function (value, index) {
                        let theName = clonedColumn.querySelector(value).getAttribute('name');
                        clonedColumn.querySelector(value).name = theName.replace(regex, loopCounter);
                    });

                    clonedColumn.querySelector('.columnName').value = '';
                    clonedColumn.querySelector('.columnType').value = '';
                    clonedColumn.querySelector('.columnNulls').checked = false;
                    clonedColumn.querySelector('.columnForeign').value = '';
                    clonedColumn.querySelector('.columnCascades').checked = false;
                    clonedColumn.querySelector('.columnAutoIncrements').checked = false;
                    clonedColumn.querySelector('.columnUnsigneds').checked = false;
                    columnsContainer.appendChild(clonedColumn);
                    window.setupDisabledColumns(clonedColumn);

                    document.getElementById('loop-counter').value = loopCounter + 1;
                } else {
                    alert('Failed to clone column');
                }
            } else {
                alert('Failed to find the first column');
            }
        } else {
            alert('Failed to find columns container');
        }
    }

    window.removeColumn = function(elem) {
         let columnsContainer = document.getElementById('repgenerator-wizzard-columns');
         if ( columnsContainer ) {
             let currentNode = elem.parentNode.closest(".migration-line");
            if ( currentNode ) {
                currentNode.remove();
            } else {
                alert('Failed to remove column');
            }
         } else {
             alert('Failed to find columns container');
         }
    }

    window.setDisabledColumn = function(disablableColumn, field, requiredValues) {
        let value, disable;
        if ( field ) {
            value = field.options ? ( field.options[field.selectedIndex] ? field.options[field.selectedIndex].text : '' ) : field.getAttribute('value');
            disable = requiredValues.split(',').indexOf(value) < 0;
            if ( disable ) {
                disablableColumn.setAttribute('disabled', 'disabled');
            } else {
                disablableColumn.removeAttribute('disabled');
            }
        }
    }

    window.setupDisabledColumn = function(disablableColumn) {
        if ( disablableColumn.getAttribute ) {
            let field, requiredClass, requiredValues;
            requiredClass = disablableColumn.getAttribute('data-required-class');
            requiredValues = disablableColumn.getAttribute('data-required-values');
            field = disablableColumn.closest('.migration-line').querySelector('.' + requiredClass)
            if ( field ) {
                field.addEventListener('change', function() {
                    window.setDisabledColumn(disablableColumn, field, requiredValues);
                });
                window.setDisabledColumn(disablableColumn, field, requiredValues)
            }
        }
    }

    window.setupDisabledColumns = function(element = null) {
        let disablableColumns = element !== null ? element.getElementsByClassName('disablable') : document.getElementsByClassName('disablable');
        let disablableColumn
        for ( let index in disablableColumns ) {
            disablableColumn = disablableColumns[index];
            window.setupDisabledColumn(disablableColumn)
        }
    }
</script>
<div class="space-y-8 divide-y divide-gray-200">
    <div id="repgenerator-wizzard-columns">
        @php($counter = 0)
        @foreach( $columns as $name => $data )
            <div class="migration-line border border-gray-300 rounded-md p-2 mb-10 bg-gray-50">
                <div class="grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12">
                    <div class="sm:col-span-2">
                        <div class="mt-1">
                            <input type="text" required="required" value="{{ $name }}" name="columnNames[]" class="columnName shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="mt-1">
                            <select name="columnTypes[]" required="required" class="columnType block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @foreach ( $columnTypes as $columnType )
                                    <option {{ $columnType == $data['type'] ? 'selected' : '' }} value="{{ $columnType }}">{{ $columnType }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-1">
                            <input type="text" placeholder="Length" value="{{ $data['length'] }}" name="columnLengths[]" class="columnLength shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-1">
                            <input type="text" placeholder="Precision" value="{{ $data['precision'] }}" data-required-class="columnType" data-required-values="{{ implode(',', ['dateTimeTz', 'dateTime', 'decimal', 'double', 'float', 'softDeletesTz', 'softDeletes', 'time', 'timeTz', 'timestamp', 'timestampTz', 'timestamps', 'timestampsTz', 'unsignedDecimal']) }}" name="columnPrecisions[{{ $loop->index }}]" class="columnPrecisions disablable disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none hadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-1">
                            <input type="text" placeholder="Scale" value="{{ $data['scale'] }}" data-required-class="columnType" data-required-values="{{ implode(',', ['decimal','double', 'float', 'unsignedDecimal']) }}" name="columnScales[]" class="disablable disablable disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none columnScale hadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-1">
                            <input type="text" placeholder="Default" value="{{ $data['default'] }}" name="columnDefaults[]" class="columnDefault hadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-3">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input name="columnNulls[{{ $loop->index }}]" {{ $data['nullable'] ? 'checked' : '' }} type="checkbox" class="columnNulls focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label class="font-medium text-gray-700">NULL</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-3">
                            <div class="relative flex items-start">
                                <select name="columnIndexes[{{ $loop->index }}][]"  class="columnIndexes block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md" multiple>
                                        <option value="primary" {{  is_array($data['index']) ? in_array('primary', $data['index']) ? 'selected' : '' : ''}}>PRIMARY</option>
                                        <option value="unique" {{  is_array($data['index']) ? in_array('unique', $data['index']) ? 'selected' : '' : ''}}>UNIQUE</option>
                                        <option value="index" {{  is_array($data['index']) ? in_array('index', $data['index']) ? 'selected' : '' : ''}}>INDEX</option>
                                        <option value="fulltext" {{  is_array($data['index']) ? in_array('fulltext', $data['index']) ? 'selected' : '' : ''}}>FULLTEXT</option>
                                        <option value="spatial" {{  is_array($data['index']) ? in_array('spatial', $data['index']) ? 'selected' : '' : ''}}>SPATIAL</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-3">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input name="columnAutoIncrements[{{ $loop->index }}]" {{ $data['auto_increment'] ? 'checked' : '' }} type="checkbox" class="columnAutoIncrements focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label class="font-medium text-gray-700">AIC</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-3">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input name="columnUnsigneds[{{ $loop->index }}]" {{ $data['unsigned'] ? 'checked' : '' }} type="checkbox" class="columnUnsigneds focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label class="font-medium text-gray-700">Unsigned</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-12 gap-y-6 gap-x-4 sm:grid-cols-12">
                    <div class="sm:col-span-5">
                        <div class="mt-3 ml-1">
                            <div class="relative flex items-start">
                                <div class="text-sm">
                                    <label class="font-medium text-gray-700">References</label>
                                </div>
                                <div class="flex items-center h-5 ml-3">
                                    <div class="mt-1">
                                        <input type="text" value="{{ $data['reference'] }}" name="columnReferences[]" class="columnReference shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="text-sm ml-3">
                                    <label class="font-medium text-gray-700">on</label>
                                </div>
                                <div class="flex items-center h-5 ml-3">
                                    <select name="columnForeigns[]" class="columnForeign block focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        <option></option>
                                        @foreach ( $models as $model )
                                            <option {{ $model == $data['foreign'] ? 'selected' : '' }}>{{ $model }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-3">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input name="columnCascades[{{ $loop->index }}]"  type="checkbox" class="columnCascades focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label class="font-medium text-gray-700">Cascade</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <div class="mt-3">
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input name="columnSearchables[{{ $loop->index }}]"  type="checkbox" class="columnSearchables focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label class="font-medium text-gray-700">Search</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="mt-1">
                            <input type="text" placeholder="Values" value="{{ $data['values'] }}" name="columnValues[]" class="columnValue shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="mt-1">
                            <input type="text" placeholder="Comment" value="{{ $data['comment'] }}" name="columnComments[]" class="columnComment shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="sm:col-span-1">
                        <button onclick="window.removeColumn(this)" class="uppercase p-3 flex items-center bg-red-500 text-white max-w-max shadow-sm hover:shadow-lg rounded-full w-10 h-10">
                            <svg width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 32 32" style="transform: rotate(360deg);"><path d="M12 12h2v12h-2z" fill="currentColor"></path><path d="M18 12h2v12h-2z" fill="currentColor"></path><path d="M4 6v2h2v20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8h2V6zm4 22V8h16v20z" fill="currentColor"></path><path d="M12 2h8v2h-8z" fill="currentColor"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            @php($counter++)
        @endforeach
        <input type="hidden" value="{{$counter}}" id="loop-counter">
        @csrf
    </div>
    <button onclick="window.addColumn()" type="button" class="inline-flex w-full justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Add column
    </button>
</div>
<script>
    window.setupDisabledColumns();
</script>
