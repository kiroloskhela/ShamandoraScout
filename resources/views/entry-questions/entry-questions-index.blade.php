@extends('layouts.app', ['pageTitle' => 'إدارة الأسئلة' ?? ''])

@section('content')
    @php
        // تهيئة البيانات للجدول كما يحتاجه المكوّن
        $rows = collect($entryQuestions)
            ->map(function ($q) {
                return [
                    'QuestionID' => $q->QuestionID,
                    'QuestionTypeInArabicWords' => $q->QuestionTypeInArabicWords,
                    'QetaaName' => $q->QetaaName,
                    'QuestionText' => $q->QuestionText,
                    'MCAnswerDisplay' => ($q->MCAnswer ?? '') !== '' ? $q->MCAnswer : 'لا يوجد اختيارات',
                    'NotToBeShownText' => (int) $q->NotToBeShown === 1 ? 'نعم' : 'لا',
                    'IsRequiredText' => (int) $q->IsRequired === 1 ? 'نعم' : 'لا',
                ];
            })
            ->toArray();
    @endphp

    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$rows" title="إدارة الأسئلة" :add-button="[
            'label' => 'إضافة سؤال جديد',
            'route' => route('entry-questions.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'QuestionID',
                'label' => 'رقم السؤال',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'QuestionTypeInArabicWords',
                'label' => 'نوع السؤال',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'QetaaName',
                'label' => 'القطاع',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
                'filter' => true,
            ],
            [
                'key' => 'QuestionText',
                'label' => 'نص السؤال',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'MCAnswerDisplay',
                'label' => 'الاختيارات المتاحة',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'NotToBeShownText',
                'label' => 'إخفاء السؤال؟',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'IsRequiredText',
                'label' => 'السؤال مطلوب؟',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => 'تعديل',
                'route' => route('entry-questions.edit', ':id'),
                'idField' => 'QuestionID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => 'مسح',
                'route' => route('entry-questions.delete', ':id'),
                'idField' => 'QuestionID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
