@extends('layouts.app', ['pageTitle' => 'الأعداد المطلوبة في فورم الالتحاق '])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$marahelLimits->toArray()" title="إدارة الأعداد المطلوبة في فورم الالتحاق" :add-button="[
            'label' => 'إضافة  ',
            'route' => route('max-limits.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'QetaaID',
                'label' => 'رقم القطاع',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'QetaaName',
                'label' => 'اسم القطاع',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
                'filter' => true,
            ],
        
            [
                'key' => 'SanaMarhalaName',
                'label' => 'رقم المرحلة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'MaxLimit',
                'label' => 'العدد المطلوب',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
        ]"
            :actions="[
                [
                    'name' => 'edit',
                    'label' => 'تعديل',
                    'route' => route('max-limits.edit', ['id' => ':id', 'sana_id' => ':sana_id']),
                    'idField' => 'QetaaID',
                    'extraFields' => [
                        'sana_id' => 'SanaMarhalaID',
                    ],
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'delete',
                    'label' => 'مسح',
                    'route' => route('max-limits.delete', ['id' => ':id', 'sana_id' => ':sana_id']),
                    'idField' => 'QetaaID',
                    'extraFields' => [
                        'sana_id' => 'SanaMarhalaID',
                    ],
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
                ],
            ]" :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection


{{-- @foreach ($marahelLimits as $marhala)
    <tr>
        <td>
            <label style="color: #4e73df; font-weight: bolder;"
                id="questionIDLabel-{{ $loop->iteration }}">{{ $marhala->QetaaID }}</label>
        </td>
        <td>
            <label style="color: #4e73df; font-weight: bolder;"
                id="qetaaIDLabel-{{ $loop->iteration }}">{{ $marhala->QetaaName }}</label>
        </td>
        <td>
            <label style="color: #4e73df; font-weight: bolder;"
                id="qetaaIDLabel-{{ $loop->iteration }}">{{ $marhala->SanaMarhalaName }}</label>
        </td>
        <td>
            <label style="color: #4e73df; font-weight: bolder;"
                id="questionTextLabel-{{ $loop->iteration }}">{{ $marhala->MaxLimit }}</label>
        </td>
        <td>
            <a href="{{ route('max-limits.edit', ['id' => $marhala->QetaaID, 'sana_id' => $marhala->SanaMarhalaID]) }}"
                style="appearance: none;
                                                                background-color: #2ea44f;
                                                                border: 1px solid rgba(27, 31, 35, .15);
                                                                border-radius: 6px;
                                                                box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                                                box-sizing: border-box;
                                                                color: #fff;
                                                                cursor: pointer;
                                                                display: inline-block;
                                                                font-size: 14px;
                                                                font-weight: 600;
                                                                line-height: 20px;
                                                                padding: 6px 16px;
                                                                position: relative;
                                                                text-align: center;
                                                                text-decoration: none;
                                                                user-select: none;
                                                                -webkit-user-select: none;
                                                                touch-action: manipulation;
                                                                vertical-align: middle;
                                                                white-space: nowrap;">
                تعديل</a>

            <a href="{{ route('max-limits.delete', ['id' => $marhala->QetaaID, 'sana_id' => $marhala->SanaMarhalaID]) }}"
                style="appearance: none;
                                                                background-color: #E21739;
                                                                border: 1px solid rgba(27, 31, 35, .15);
                                                                border-radius: 6px;
                                                                box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
                                                                box-sizing: border-box;
                                                                color: #fff;
                                                                cursor: pointer;
                                                                display: inline-block;
                                                                font-size: 14px;
                                                                font-weight: 600;
                                                                line-height: 20px;
                                                                padding: 6px 16px;
                                                                position: relative;
                                                                text-align: center;
                                                                text-decoration: none;
                                                                user-select: none;
                                                                -webkit-user-select: none;
                                                                touch-action: manipulation;
                                                                vertical-align: middle;
                                                                white-space: nowrap;">
                مسح</a>
        </td>
    </tr>
@endforeach --}}
