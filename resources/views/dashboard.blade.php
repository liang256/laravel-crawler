<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    You're logged in!
                </div>
            </div>
            
            <div class="bg-white mt-4 overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-4 border">
                    共 {{ $models->count() }} 筆資料
                </div>

                <table class="table table-hover p-4 border">
                    <thead>
                        <tr>
                            <th class="p-1 border" scope="col" style="width: 8%;">日期</th>
                            <th class="p-1 border" scope="col" style="width: 4%;">星座</th>
                            <th class="p-1 border" scope="col" style="width: 3%;">整體運勢分數</th>
                            <th class="p-1 border" scope="col" style="width: 12%;">整體運勢</th>
                            <th class="p-1 border" scope="col" style="width: 3%;">愛情運勢分數</th>
                            <th class="p-1 border" scope="col" style="width: 12%;">愛情運勢</th>
                            <th class="p-1 border" scope="col" style="width: 3%;">事業運勢分數</th>
                            <th class="p-1 border" scope="col" style="width: 12%;">事業運勢</th>
                            <th class="p-1 border" scope="col" style="width: 3%;">財運運勢分數</th>
                            <th class="p-1 border" scope="col" style="width: 12%;">財運運勢</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($models as $model)
                        <tr>
                            <td class="p-2 border text-center">{{ $model->time_range }}</td>
                            <td class="p-2 border text-center">{{ $model->name }}</td>
                            <td class="p-2 border text-center">{{ $model->general_score }}</td>
                            <td class="p-2 border">{{ $model->general_fortune }}</td>
                            <td class="p-2 border text-center">{{ $model->love_score }}</td>
                            <td class="p-2 border">{{ $model->love_fortune }}</td>
                            <td class="p-2 border text-center">{{ $model->career_score }}</td>
                            <td class="p-2 border">{{ $model->career_fortune }}</td>
                            <td class="p-2 border text-center">{{ $model->wealth_score }}</td>
                            <td class="p-2 border">{{ $model->wealth_fortune }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

        </div>
    </div>


</x-app-layout>
